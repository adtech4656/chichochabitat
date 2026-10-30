<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class PanelSessionIsolationTest extends TestCase
{
    public function test_admin_and_agent_panels_use_distinct_path_scoped_session_cookies(): void
    {
        $publicCookieName = config('session.cookie');
        $publicCookiePath = config('session.path');

        $adminResponse = $this->get('/admin/login')->assertOk();
        $adminCookie = $this->findCookie($adminResponse->baseResponse->headers->getCookies(), 'cch_admin_session');

        $this->assertNotNull($adminCookie);
        $this->assertSame('/admin', $adminCookie->getPath());
        $this->assertTrue($adminCookie->isHttpOnly());
        $this->assertSame(Cookie::SAMESITE_STRICT, $adminCookie->getSameSite());
        $this->assertSame($publicCookieName, config('session.cookie'));
        $this->assertSame($publicCookiePath, config('session.path'));

        $agentResponse = $this->get('/agent/login')->assertOk();
        $agentCookie = $this->findCookie($agentResponse->baseResponse->headers->getCookies(), 'cch_agent_session');

        $this->assertNotNull(
            $agentCookie,
            'Agent response cookies: '.implode(', ', array_map(
                fn (Cookie $cookie) => $cookie->getName().'@'.$cookie->getPath(),
                $agentResponse->baseResponse->headers->getCookies(),
            )),
        );
        $this->assertSame('/agent', $agentCookie->getPath());
        $this->assertTrue($agentCookie->isHttpOnly());
        $this->assertSame(Cookie::SAMESITE_STRICT, $agentCookie->getSameSite());
        $this->assertSame($publicCookieName, config('session.cookie'));
        $this->assertSame($publicCookiePath, config('session.path'));
    }

    /**
     * @param  array<int, Cookie>  $cookies
     */
    private function findCookie(array $cookies, string $name): ?Cookie
    {
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }
}
