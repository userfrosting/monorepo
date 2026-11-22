<?php

declare(strict_types=1);

namespace Workers\MonoRepo;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

final class NpmVersionWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ProcessRunner $processRunner,
    ) {
    }

    public function work(Version $version) : void
    {
        $npmVersionCommand = \sprintf('npm version %s --workspaces --git-tag-version false --allow-same-version', $version->getVersionString());
        $this->processRunner->run($npmVersionCommand);
    }

    public function getDescription(Version $version) : string
    {
        return \sprintf('Set npm version "%s"', $version->getVersionString());
    }
}
