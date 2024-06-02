<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DebugJwtCommand extends Command
{
    protected static $defaultName = 'app:debug-jwt';

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        parent::__construct();
        $this->params = $params;
    }

    protected function configure()
    {
        $this
            ->setDescription('Debug JWT configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $privateKey = file_get_contents($this->params->get('jwt_secret_key'));
        $publicKey = file_get_contents($this->params->get('jwt_public_key'));
        $passPhrase = $this->params->get('jwt_passphrase');

        $io->success('JWT configuration loaded successfully');
        $io->text([
            'Private Key: ' . ($privateKey ? 'Loaded' : 'Not Loaded'),
            'Public Key: ' . ($publicKey ? 'Loaded' : 'Not Loaded'),
            'Pass Phrase: ' . $passPhrase,
        ]);

        return Command::SUCCESS;
    }
}