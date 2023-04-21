<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Container\ContainerInterface;
use DeepCopy\Filter\Doctrine;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Genre;
use App\Form\ProductType;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ProductController extends AbstractController
{
    #[Route('/', name: 'product_list')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
        $products = @$doctrine->getRepository('App\Entity\Product')->findAll();
        // $products = $doctrine->getRepository('App\Entity\Product')->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }
    #[Route('/details/{id}', name: 'product_details')]
    public function detailsAction(ManagerRegistry $doctrine,$id): Response
    {
        $products =  $doctrine->getRepository('App\Entity\Product')->find($id);


        return $this->render('product/details.html.twig', [
            'products' => $products
        ]);
    }
    /**
     * @route("/delete/{id}", name="product_delete")
     */
    public function deleteAction(ManagerRegistry $doctrine,$id)
    {


        $em = $doctrine->getManager();
        $product = $em->getRepository('App\Entity\Product')->find($id);
        $em->remove($product);
        $em->flush();

        $this->addFlash(
            'error',
            'Product deleted'
        );
        return $this->redirectToRoute('product_list');
    }
    /**
     * @Route("/create/", name="product_create", methods={"GET","POST"})
     */
    public function createAction(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $products = new Product();

        $form = $this->createForm(ProductType::class, $products);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image=$form->get('Image')->getData();
            if($image){
                $originalFilename=pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename= $slugger ->slug($originalFilename);
                $newFilename=$safeFilename . '-' . uniqid() . '.' . $image ->guessExtension();

                try{
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                }catch(FileException $e){
                    $this->addFlash(
                        'error',
                        'Cannot upload'
                    );
                }
                $products->setImage($newFilename);
            }else{
                $this->addFlash(
                    'error',
                    'Cannot upload'
                );
            }
            $em = $doctrine->getManager();
            $em->persist($products);
            $em->flush();


            $this->addFlash(
                'notice',
                'Product Added'
            );

            return $this->redirectToRoute('product_list');
        }

        return $this->renderForm('product/create.html.twig', ['form' => $form,]);
    }
    /**
     * @Route("/product/edit/{id}", name="product_edit")
     */
    public function edit(ManagerRegistry $doctrine, int $id, Request $request, SluggerInterface $slugger): Response
    {
        $entitymanager = $doctrine->getManager();
        $products = $entitymanager->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductType::class, @$prodcuts);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('Image')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Move the file to the directory where image are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Cannot Upload'
                    );
                }
            }
            $products->setImage($newFilename);
            $entitymanager = $doctrine->getManager();
            $entitymanager->persist($products);
            $entitymanager->flush();

            return $this->redirectToRoute('products_list', [
                'id' => $products->getId()
            ]);
        }
        return $this->renderForm('product/edit.html.twig', ['form' => $form,]);
    }
    public function saveChanges(ManagerRegistry $doctrine, $form, $request, $products)
    {
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $products->setproductName($request->request->get('products')['name']);
            $products->setproductDescription($request->request->get('products')['description']);
            $products->setproductPrice($request->request->get('products')['price']);
            $entitymanager = $doctrine->getManager();
            $entitymanager->persist($products);
            $entitymanager->flush();

            return true;
        }

        return false;
    }



}
