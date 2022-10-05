<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\Middleware;

use Mpie\Http\Message\Contract\HeaderInterface;
use Mpie\Http\Message\Cookie;
use Mpie\Session\Manager;
use Mpie\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Some attribute annotations are from MDN: https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies.
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * A session cookie is the simplest type of cookie: it is automatically
     * deleted when the browser is closed, which means it is only valid for
     * the duration of the session. Session cookies do not need to specify
     * an expiration time (Expires) or a validity period (Max-Age). It should
     * be noted that some browser provide a session recovery function. In this case,
     * even if the browser is closed, the session cookie will be retained,
     * as if the browser was never closed, which will cause the cookie to have an
     * infinite life cycle. period extended. The life cycle of persistent cookies depends
     * on the period of time specified by the expiration time (Expires) or the validity
     * period (Max-Age).
     */
    protected int $expires = 9 * 3600;

    /**
     * Session cookie name.
     */
    protected string $name = 'MPIE_SESS_ID';

    /**
     * There are two ways to ensure that cookies are sent securely and cannot be accessed
     * by unintended actors or scripts: the Secure attribute and the HttpOnly attribute.
     * Cookies marked as Secure should only be sent to the server through requests encrypted
     * by the HTTPS protocol, thus preventing man-in-the-middle attackers. But even if the
     * Secure flag is set, sensitive information should not be transmitted through cookies,
     * because cookies are inherently insecure, and the Secure flag does not provide real
     * security, for example, that someone with access to the client's hard drive can read it.
     * The JavaScript Document.cookie API cannot access cookies with the HttpOnly attribute;
     * such cookies are server-only. For example, cookies that persist server-side sessions
     * do not need to be available to JavaScript, but should have an HttpOnly attribute. This
     * precaution helps mitigate Cross-Site Scripting (XSS) (en-US) attacks.
     */
    protected bool $httponly = true;

    /**
     * The Path identifier specifies which paths under the host can accept cookies
     * (the URL path must exist in the request URL). Subpaths are also matched with
     * the characters %x2F ("/") as the path separator.
     *
     * For example, with Path=/docs, the following addresses will all match:
     * /docs
     * /docs/Web/
     * /docs/Web/HTTP
     */
    protected string $path = '/';

    /**
     * Domain specifies which hosts can accept cookies. If not specified, it defaults to origin,
     * excluding subdomains. If Domain is specified, subdomains are generally included. Therefore,
     * specifying Domain is less restrictive than omitting it. However, this can be helpful when
     * subdomains need to share information about users. For example, if Domain=mozilla.org is set,
     * cookies are also included in subdomains (eg developer.mozilla.org).
     */
    protected string $domain = '';

    /**
     * Cookies marked as Secure should only be sent to the server through requests encrypted by the
     * HTTPS protocol, thus preventing man-in-the-middle attackers. But even if the Secure flag is set,
     * sensitive information should not be transmitted through cookies, because cookies are inherently
     * insecure, and the Secure flag cannot provide real security. For example, someone with access to
     * the client's hard drive can read it.
     */
    protected bool $secure = true;

    /**
     * SameSite cookies prevent Cross-Site Request Forgery (CSRF) attacks by allowing the server to require
     * a cookie not to be sent on cross-site requests (where Site (en-US) is defined by a registrable domain).
     * SameSite can have the following three values:
     * None. The browser will continue to send cookies under the same-site request and cross-site request, which is not case-sensitive.
     * Strict. The browser will only send the cookie when visiting the same site. (Enhancing the limitations of the original cookies, as described in "Cookie Scope" above)
     * Lax. Similar to Strict, except when the user navigates to the URL from an external site (for example, via a link). In newer versions of browsers, the default option, Same-site cookies will be reserved for some cross-site subrequests, such as image loading or calls to frames, but will only be sent when the user navigates to the URL from an external site. as link link.
     */
    protected string $sameSite = Cookie::SAME_SITE_LAX;

    public function __construct(
        protected Manager $manager
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $this->manager->create();
        $session->start($request->getCookieParams()[strtoupper($this->name)] ?? '');
        $request  = $request->withAttribute('Mpie\Session\Session', $session);
        $response = $handler->handle($request);
        $session->save();
        $session->close();

        return $this->addCookieToResponse($response, $session);
    }

    /**
     * Add cookies to the response.
     */
    protected function addCookieToResponse(ResponseInterface $response, Session $session): ResponseInterface
    {
        $expires = $session->isDestroyed() ? -1 : time() + $this->expires;
        $cookie  = new Cookie($this->name, $session->getId(), $expires, $this->path, $this->domain, $this->secure, $this->httponly, $this->sameSite);

        return $response->withAddedHeader(HeaderInterface::HEADER_SET_COOKIE, $cookie->__toString());
    }
}
