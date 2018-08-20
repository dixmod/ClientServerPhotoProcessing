<?php
declare(strict_types=1);

namespace Dixmod\Command;

use Dixmod\{
    File\Photo, Sockets\Client
};
use Symfony\Component\Console\{
    Command\Command, Input\InputArgument, Input\InputInterface, Output\OutputInterface, Question\ChoiceQuestion
};

class Processing extends Command
{
    protected const FILTER_NAMES = [
        'sepia',
        'negate',
        'grayscale',
        'emboss',
        'mean_removal',
        'gaussian_blur',
    ];
    protected $input;
    protected $output;
    protected $dialog;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('processing')
            ->setDescription('This command processing photos')
            ->addArgument(
                'filePhoto',
                InputArgument::REQUIRED,
                'Path to photo file'
            )
            ->addArgument(
                'filterName',
                InputArgument::OPTIONAL,
                'Change filter for photo from list: ' . join(', ', self::FILTER_NAMES),
                ''
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->dialog = $this->getHelper('question');

        $inputFilterName = $this->askFilterName();
        $filePhoto = $this->input->getArgument('filePhoto');
        $message = [
            'fileName' => basename($filePhoto),
            'filterName' => $inputFilterName,
            'fileContent' => file_get_contents($filePhoto)
        ];

        $client = new Client();
        $client->setMessage(base64_encode(serialize($message)));
        $resultFileContent = $client->run();

        file_put_contents(
            basename($filePhoto),
            $resultFileContent
        );
    }

    /**
     * @param string $inputFilterName
     * @return bool
     */
    protected function isValidFilterName(string $inputFilterName)
    {
        return in_array($inputFilterName, self::FILTER_NAMES);
    }

    /**
     * @return string
     */
    private function askFilterName(): string
    {
        $inputFilterName = $this->input->getArgument('filterName');
        $inputFilterName = strtolower($inputFilterName);
        if (!$this->isValidFilterName($inputFilterName)) {
            $question = new ChoiceQuestion(
                '<question>Please select filter for photo:</question>',
                self::FILTER_NAMES
            );
            $question->setErrorMessage('Filter %s is invalid.');
            $inputFilterName = $this->dialog->ask($this->input, $this->output, $question);
        }
        return $inputFilterName;
    }
}