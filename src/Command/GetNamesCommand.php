<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'get_names')]
class GetNamesCommand extends Command
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Получает имена персонажей из эпизода сериала "Рик и Морти"')
            ->addArgument('episode', InputArgument::REQUIRED, 'Номер эпизода');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $episode = $input->getArgument('episode');

        $response = $this->client->request('GET', "https://rickandmortyapi.com/api/episode/$episode");

        $data = $response->toArray();

        $output->writeln("Список персонажей $episode эпизода Рика и Морти:");

        foreach ($data['characters'] as $index => $characterUrl) {
            $response = $this->client->request('GET', $characterUrl);
            $characterData = $response->toArray();

            $output->writeln('- ' . ($index + 1) . ') ' . $characterData['name']);
        }

        return Command::SUCCESS;
    }
}
