<?php
declare(strict_types=1);

namespace App\Service\Helper;

use App\Entity\Contact;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadHelper
{
    private $logger;
    private $uploadPath;

    public function __construct(LoggerInterface $logger, string $uploadPath)
    {
        $this->logger = $logger;
        $this->uploadPath = $uploadPath;
    }

    public function upload(Contact $contact, UploadedFile $pictureFile): void
    {
        $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);

        if (\extension_loaded('intl')) {
            // https://symfony.com/doc/3.4/controller/upload_file.html
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate(
                'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                $originalFilename
            );

            $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $pictureFile->guessExtension();
        } else {
            // https://symfony.com/doc/3.3/controller/upload_file.html
            $newFilename = md5(uniqid('', true)) . '.' . $pictureFile->guessExtension();
        }


        // move the file to the directory where brochures are stored
        try {
            $pictureFile->move(
                $this->uploadPath,
                $newFilename
            );
        } catch (FileException $exception) {
            $this->logger->debug($exception->getMessage());
        }

        $contact->setPicture($newFilename);
    }
}
