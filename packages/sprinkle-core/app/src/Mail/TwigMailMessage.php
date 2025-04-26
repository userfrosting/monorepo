<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Mail;

use Slim\Views\Twig;
use Twig\Template;

/**
 * Represents a Twig formatted mail message.
 */
final class TwigMailMessage extends AbstractMailMessage implements MailMessage
{
    /**
     * @var mixed[] A list of Twig placeholder values to use when rendering this message.
     */
    protected array $params;

    /**
     * Create a new TwigMailMessage instance.
     *
     * @param Twig   $view     The Twig view object used to render mail templates.
     * @param string $template optional Set the Twig template to use for this message.
     */
    public function __construct(protected Twig $view, protected string $template = '')
    {
        $twig = $this->view->getEnvironment();
        $this->params = $twig->getGlobals();
    }

    /**
     * Merge in any additional global Twig variables to use when rendering this message.
     *
     * @param mixed[] $params
     *
     * @return static
     */
    public function addParams(array $params = []): static
    {
        $this->params = array_replace_recursive($this->params, $params);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function renderSubject($params = []): string
    {
        $params = array_replace_recursive($this->params, $params);

        return $this->view->fetchBlock($this->template, 'subject', $params);
    }

    /**
     * {@inheritdoc}
     */
    public function renderBody($params = []): string
    {
        $params = array_replace_recursive($this->params, $params);

        return $this->view->fetchBlock($this->template, 'body', $params);
    }

    /**
     * Sets the Twig template object for this message.
     *
     * @param string $template The Twig template object, to source the content for this message.
     *
     * @return static
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Return the currently defined template filename.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}
