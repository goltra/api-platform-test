<?php

namespace App\Controller;

use App\Entity\Tiempo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

class TiempoController extends AbstractController
{

    public function __invoke(Tiempo $data): Tiempo
    {
        $data->setDescripcion('llamado desde controlador personalizado');
        return $data;
    }
}
