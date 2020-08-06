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

        // this is needed to safely include the file name as part of the URL
        $newFilename = $originalFilename . '_' . date('Y_m_d_H_i_s') . '.' . $pictureFile->guessExtension();

        // Move the file to the directory where brochures are stored
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
