<?php

namespace App\Command\DeleteData;

use App\Entity\Article;
use App\Entity\Item;
use App\Entity\ItemRegistration;
use App\Entity\News;
use App\Entity\NewsTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class MoveNewsCommand extends Command
{
    protected static $defaultName = 'app:move:news';

    protected const ITEM_DELETED = 'deleted';
    protected const ITEM_MOVED = 'moved';
    protected const ITEM_FAILED = 'failed';
    protected const NEWS_SIMILAR_PROCESSED = 'news_similar_processed';
    protected const NEWS_SIMILAR_EMPTY = 'news_similar_empty';

    protected $newsAndArcticleIdsPair = [];

    protected $arcticleAndNewsIdsPair = [];

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Application data move: move Article with type news from article table to news table')
            ->setHelp('Run Application data delete process for Article with News type.
             This command should be run only once on production.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            self::ITEM_MOVED => 0,
            self::ITEM_DELETED => 0,
            self::ITEM_FAILED => 0,
            self::NEWS_SIMILAR_PROCESSED => 0,
            self::NEWS_SIMILAR_EMPTY => 0
        ];

        try {
            $output->writeln('Required data initializing...');
            $news = $this->selectNewsFromArticles();
            $newsIds = $this->selectNewsIdsFromArticles();

        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($news) as $item) {
            try {
                $result = $this->processItem($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_FAILED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
                print_r($exception->getTraceAsString());
            }
        }

        $outputData[self::ITEM_DELETED] = count($newsIds);
        $output->writeln('File data processing finished');
        $output->writeln('News Similar Data processing...');

        try {
            $output->writeln('Required data initializing...');
            $news = $this->selectNews();
            $this->arcticleAndNewsIdsPair = array_flip($this->newsAndArcticleIdsPair);
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($news) as $item) {
            try {
                $result = $this->processSimilarNews($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_FAILED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
                print_r($exception->getTraceAsString());
            }
        }
        $this->deleteArticleWithTypeNewsFromItemTable($newsIds);
        $output->writeln("Items deleted: {$outputData[self::ITEM_DELETED]}");
        $output->writeln('News Similar data processing finished');
        $output->writeln("News Similar processed: {$outputData[self::NEWS_SIMILAR_PROCESSED]}");
        $output->writeln("News without similar relation: {$outputData[self::NEWS_SIMILAR_EMPTY]}");

        return Command::SUCCESS;
    }

    public function processItem($item)
    {
        $news = new News();
        $news->setIsActive($item->getIsActive());
        $news->setSlug($item->getSlug());
        $news->setRegistrationRequired($item->getRegistrationRequired() ?? false);
        $news->setCommentsAllowed($item->getCommentsAllowed() ?? false);
        $news->setFeedbackForm($item->getFeedbackForm() ?? null);
        $news->setViewsAmount($item->getViewsAmount() ?? 0);
        $news->setCategory($item->getCategory() ?? null);
        $news->setTop($item->getTop() ?? null);
        $news->setCreatedAt($item->getCreatedAt());
        $news->setUpdatedAt($item->getUpdatedAt() ?? $item->getCreatedAt());


        $news->setImageName($item->getImageName() ?? null);
        $news->setRegion($item->getRegion() ?? null);
        $this->setNewsCollections($news, $item);
        $this->setNewsTranslations($news, $item);

        $this->entityManager->persist($news);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->newsAndArcticleIdsPair[$news->getId()] = $item->getId();

        return self::ITEM_MOVED;
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This command should be run only once on production. Continue with this action? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
    }

    private function processSimilarNews($news):string
    {
        $similarArticleIds = $this->selectSimilarArticlesIdsFromArticleArticleTable($news);
        if (empty($similarArticleIds)) {
            return self::NEWS_SIMILAR_EMPTY;
        }
        foreach ($similarArticleIds as $similarArticleId) {
            if (array_key_exists($similarArticleId, $this->arcticleAndNewsIdsPair)) {
                $similarNews = $this->entityManager->getRepository(News::class)
                    ->find($this->arcticleAndNewsIdsPair[$similarArticleId]);
                $news->addSimilar($similarNews);
            }else{
                $news->addSimilar($this->entityManager->getRepository(Article::class)->find($similarArticleId));
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return self::NEWS_SIMILAR_PROCESSED;
    }

    private function selectSimilarArticlesIdsFromArticleArticleTable($news): array
    {
        $ids = [];

        $similarArticleId = "SELECT article_target from article_article WHERE article_source = {$this->newsAndArcticleIdsPair[$news->getId()]}";
        $statement = $this->entityManager->getConnection()->prepare($similarArticleId);
        $statement->execute();
        $result = $statement->fetchAll();
        if (!empty($result)) {
            foreach ($result as $item) {
                $ids[] = $item['article_target'];
            }
        }
        return $ids;
    }

    private function selectNewsFromArticles(): iterable
    {
        return $this->entityManager->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->join('a.typePage', 'tp')
            ->andWhere('tp.code = :news')
            ->setParameter('news', 'news')
            ->getQuery()->toIterable();
    }

    public function selectNewsIdsFromArticles(): array
    {
        $q = $this->entityManager->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->join('a.typePage', 'tp')
            ->select('a.id')
            ->andWhere('tp.code = :news')
            ->setParameter('news', 'news')
            ->getQuery()->getArrayResult();
        foreach ($q as $item) {
            $ids[] = $item['id'];
        }
        return $ids;
    }

    private function selectNews()
    {
        return $this->entityManager->getRepository(News::class)
            ->createQueryBuilder('n')->select('n')
            ->getQuery()->toIterable();
    }

    private function deleteArticleWithTypeNewsFromItemTable(array $ids): void
    {
        $this->entityManager->getRepository(Item::class)->createQueryBuilder('i')->delete()
            ->where('i.id IN (:newsIds)')
            ->setParameter('newsIds', $ids)
            ->getQuery()->execute();
    }

    private function setNewsCollections(News $news, Article $item)
    {
        $this->setNewsComments($news, $item);
        $this->setNewsTags($news, $item);
        $this->setNewsPartners($news, $item);
        $this->setNewsExperts($news, $item);
        $this->setNewsCrops($news, $item);
    }

    private function setNewsComments(News $news, Article $item): void
    {
        if (!empty($item->getComments())) {
            foreach ($item->getComments() as $value) {
                $news->addComment($value);
            }
        }
    }

    private function setNewsTags(News $news, Article $item): void
    {
        if (!empty($item->getTags())) {
            foreach ($item->getTags() as $value) {
                $news->addTag($value);
            }
        }
    }

    private function setNewsPartners(News $news, Article $item): void
    {
        if (!empty($item->getPartners())) {
            foreach ($item->getPartners() as $value) {
                $news->addPartner($value);
            }
        }
    }

    private function setNewsExperts(News $news, Article $item): void
    {
        if (!empty($item->getExperts())) {
            foreach ($item->getExperts() as $value) {
                $news->addExpert($value);
            }
        }
    }

    private function setNewsCrops(News $news, Article $item): void
    {
        if (!empty($item->getCrops())) {
            foreach ($item->getCrops() as $value) {
                $news->addCrop($value);
            }
        }
    }

    private function setNewsTranslations(News $news, Article $item): void
    {
        $newsEn = new NewsTranslation();
        $newsUk = new NewsTranslation();
        $newsEn->setTranslatable($news);
        $newsUk->setTranslatable($news);

        $newsUk->setLocale('uk');
        $newsUk->setTitle($item->getTranslations()['uk']->getTitle() ?? '');
        $newsUk->setContent($item->getTranslations()['uk']->getContent() ?? null);
        $newsUk->setShort($item->getTranslations()['uk']->getShort() ?? null);
        $newsUk->setKeywords($item->getTranslations()['uk']->getKeywords() ?? null);
        $newsUk->setDescription($item->getTranslations()['uk']->getDescription() ?? null);

        $newsEn->setLocale('en');
        if (array_key_exists('en', $item->getTranslations())) {
            $newsEn->setTitle($item->getTranslations()['en']->getTitle() ?? '');
            $newsEn->setContent($item->getTranslations()['en']->getContent() ?? null);
            $newsEn->setShort($item->getTranslations()['en']->getShort() ?? null);
            $newsEn->setKeywords($item->getTranslations()['en']->getKeywords() ?? null);
            $newsEn->setDescription($item->getTranslations()['en']->getDescription() ?? null);
        } else {
            $newsEn->setTitle('');
        }


        $this->entityManager->persist($newsUk);
        $this->entityManager->persist($newsEn);
    }
}