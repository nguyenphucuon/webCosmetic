<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryController extends AbstractController
{
    /**
     * @Route("admin/category/create", name="app_category")
     */
    public function create(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entitymanager = $doctrine->getManager();
            $entitymanager->persist($form->getData());
            $entitymanager->flush();

            $this->addFlash(
                'notice',
                'New Category Added'
            );
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}