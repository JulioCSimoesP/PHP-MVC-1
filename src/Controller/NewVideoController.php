<?php

namespace juliocsimoesp\PHPMVC1\Controller;

use DomainException;
use InvalidArgumentException;
use juliocsimoesp\PHPMVC1\Model\Domain\Entity\Video;
use juliocsimoesp\PHPMVC1\Model\Infrastructure\Service\RedirectionManager;
use juliocsimoesp\PHPMVC1\Model\Infrastructure\Service\UploadManager;

class NewVideoController extends Controller implements RequestController
{

    public function processRequest(): void
    {
        if (!isset($_POST['url']) || !isset($_POST['titulo'])) {
            RedirectionManager::redirect(responseCode: 400);
        }

        try {

            $video = new Video(
                $_POST['url'],
                $_POST['titulo']
            );

            UploadManager::processImageUpload($video);
            $operationSuccess = $this->videoRepository->addVideo($video);

            if ($operationSuccess) {
                RedirectionManager::redirect(responseCode: 303, params: ['sucesso' => 1]);
            } else {
                RedirectionManager::redirect(responseCode: 500);
            }

        } catch (InvalidArgumentException | DomainException $exception) {

            RedirectionManager::redirect(responseCode: 303, params: ['erro' => 1]);

        }
    }
}