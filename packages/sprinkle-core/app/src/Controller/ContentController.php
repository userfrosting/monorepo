<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Sprinkle\Core\Util\Markdown;

/**
 * Return the content of any Markdown file stored in the <content> directory.
 *
 * Middleware: none
 * Route: /c/{path:.+}[/]
 * Route Name: api.content
 * Request type: GET
 */
class ContentController
{
    public function __construct(
        protected Markdown $markdown
    ) {
    }

    /**
     * Invoke the content controller.
     *
     * @param string   $path     The markdown file to retrieve, defined by the router.
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(string $path, Response $response): Response
    {
        // If path end with a slash, remove it
        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, -1);
        }

        $file = $this->markdown->readFile($path);
        $response->getBody()->write(json_encode([
            'metadata' => $file->frontMatter,
            'content'  => $file->content,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
