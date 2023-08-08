<?php

namespace juliocsimoesp\PHPMVC1\Model\Infrastructure\Repository;

use DomainException;
use juliocsimoesp\PHPMVC1\Model\Domain\Entity\Video;
use juliocsimoesp\PHPMVC1\Model\Domain\Repository\VideoRepository;
use PDO;

class PdoVideoRepository extends PdoRepository implements VideoRepository
{

    public function addVideo(Video $video): bool
    {
        $this->verifyDuplicate($video);

        $insertQuery = "INSERT INTO videos (url, title) VALUES (:url, :title);";
        $statement = $this->pdo->prepare($insertQuery);
        $statement->bindValue(':url', $video->url);
        $statement->bindValue(':title', $video->title);
        $result = $statement->execute();

        $video->setId($this->pdo->lastInsertId());

        return $result;
    }

    public function removeVideo(int $id): bool
    {
        $this->verifyId($id);

        $deleteQuery = "DELETE FROM videos WHERE id = ?;";
        $statement = $this->pdo->prepare($deleteQuery);
        $statement->bindValue(1, $id);

        return $statement->execute();
    }

    public function updateVideo(Video $video): bool
    {
        $this->verifyId($video->id);

        $updateQuery = "UPDATE videos SET url = :url, title = :title WHERE id = :id;";
        $statement = $this->pdo->prepare($updateQuery);
        $statement->bindValue(':url', $video->url);
        $statement->bindValue(':title', $video->title);
        $statement->bindValue(':id', $video->id, PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * @return Video[]
     */
    public function allVideos(): array
    {
        $readQuery = "SELECT * FROM videos;";
        $statement = $this->pdo->prepare($readQuery);
        $statement->execute();
        $queryResult = $statement->fetchAll();

        return array_map(function (array $videoData) {
            return $this->hydrateVideo($videoData);
        }, $queryResult);
    }

    public function videoById(int $id): Video
    {
        $this->verifyId($id);

        $readQuery = "SELECT * FROM videos WHERE id = ?;";
        $statement = $this->pdo->prepare($readQuery);
        $statement->bindValue(1, $id);
        $statement->execute();
        $queryResult = $statement->fetch();

        return $this->hydrateVideo($queryResult);
    }

    private function verifyDuplicate(Video $video): void
    {
        $readQuery = "SELECT * FROM videos WHERE url = ?;";
        $statement = $this->pdo->prepare($readQuery);
        $statement->bindValue(1, $video->url);
        $statement->execute();

        if ($statement->fetch(PDO::FETCH_ASSOC) !== false) {
            throw new DomainException('URL informada já existe.');
        }
    }

    private function verifyId(int $id): void
    {
        $readQuery = "SELECT * FROM videos WHERE id = ?;";
        $statement = $this->pdo->prepare($readQuery);
        $statement->bindValue(1, $id);
        $statement->execute();

        if ($statement->fetch(PDO::FETCH_ASSOC) === false) {
            throw new DomainException('ID informado não existe.');
        }
    }

    /**
     * @param string[] $videoData
     * @return Video
     */
    private function hydrateVideo(array $videoData): Video
    {
            $video = new Video(
                $videoData['url'],
                $videoData['title']
            );
            $video->setId($videoData['id']);

            return $video;
    }
}