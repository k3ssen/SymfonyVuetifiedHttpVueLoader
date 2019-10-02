<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index()
    {
        return $this->render('dashboard/index.vue.twig');
    }
}
