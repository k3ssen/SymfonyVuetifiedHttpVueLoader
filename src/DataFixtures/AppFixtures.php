<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use FOS\UserBundle\Model\UserManagerInterface;

class AppFixtures extends Fixture
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        $this->createAdmin();
    }

    protected function createAdmin()
    {
        /** @var User $admin */
        $admin = $this->userManager->createUser();
        $admin->setUsername('admin')
            ->setEmail('admin@app.test')
            ->setPlainPassword('admin')
            ->setEnabled(true)
            ->setSuperAdmin(true)
        ;
        $this->userManager->updateUser($admin);
    }
}