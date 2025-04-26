<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig\Extensions;

use donatj\UserAgent\UserAgentParser;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extension to provide user agent information and utilities as global
 * variables and filters in templates.
 *
 * The `user_agent` global variable contains the user agent string from the
 * server's global variables, while the `user_ip` global variable contains the
 * user's IP address. The user agent string is parsed using donatj/PhpUserAgent.
 * 
 * @see https://github.com/donatj/PhpUserAgent
 */
class UserAgentExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param UserAgentParser $parser The user agent parser service
     */
    public function __construct(
        protected UserAgentParser $parser,
    ) {
    }

    /**
     * Adds Twig global variables `site`.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'user_agent' => $this->getUserAgent(),
        ];
    }

    /**
     * Retrieves the user agent information.
     *
     * This includes:
     * - 'ip': The user's IP address.
     * - 'browser': The name of the user's browser.
     * - 'version': The version of the user's browser.
     * - 'platform': The user's operating system or platform.
     *
     * @return array{
     *     ip: string,
     *     browser: string,
     *     version: string,
     *     platform: string
     * } An associative array containing details about the user agent.
     */
    protected function getUserAgent(): array
    {
        $parser = $this->parser->parse();

        return [
            'ip'       => $this->getUserIp(),
            'browser'  => $parser->browser() ?? '',
            'version'  => $parser->browserVersion() ?? '',
            'platform' => $parser->platform() ?? '',
        ];
    }

    /**
     * Retrieves the user ip from the server's global variables.
     *
     * @return string
     */
    protected function getUserIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}
