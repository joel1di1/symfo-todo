<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Faker\Factory;

final class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/tasks';
    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Task::class);
        $this->faker = Factory::create();

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        // create a new task and persist it to the database
        $task = new Task();
        $task->setTitle($this->faker->sentence());
        $task->setDescription($this->faker->paragraph());

        $this->manager->persist($task);
        $this->manager->flush();

        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Todo List');

        // Use assertions to check that the task is properly displayed.
        $this->assertSelectorTextContains('h1', 'To-Do List');
        // assert that the task is displayed in the page css exemple: #tasks #task_1
        $this->assertSelectorTextContains(sprintf("#task_%d", $task->getId()), $task->getTitle());
    }

    public function testNewFromList(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        self::assertResponseStatusCodeSame(200);

        $taskTitle = $this->faker->sentence();
        $this->client->submitForm('Add', [
            'task[title]' => $taskTitle,
        ]);

        self::assertSame(1, $this->repository->count([]));
        $taskDb = $this->repository->findAll()[0];
        $this->assertSelectorTextContains(sprintf("#task_%d", $taskDb->getId()), $taskTitle);
        $this->assertSame($taskTitle, $taskDb->getTitle());
    }

    public function testShow(): void
    {
        $fixture = new Task();
        $fixture->setTitle($this->faker->sentence());
        $fixture->setDescription($this->faker->paragraph());
        $fixture->setStatus($this->faker->word());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', $this->path);

        $this->client->request('GET', sprintf('%s/%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Task');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Task();
        $fixture->setTitle($this->faker->sentence());
        $fixture->setDescription($this->faker->paragraph());
        $fixture->setDue_date($this->faker->dateTime);
        $fixture->setStatus($this->faker->word());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'task[title]' => 'Something New',
            'task[description]' => 'Something New',
            'task[due_date]' => 'Something New',
            'task[status]' => 'Something New',
        ]);

        self::assertResponseRedirects('/task/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDue_date());
        self::assertSame('Something New', $fixture[0]->getStatus());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Task();
        $fixture->setTitle($this->faker->sentence());
        $fixture->setDescription($this->faker->paragraph());
        $fixture->setDue_date($this->faker->dateTime);
        $fixture->setStatus($this->faker->word());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/task/');
        self::assertSame(0, $this->repository->count([]));
    }
}
