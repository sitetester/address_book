<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\Helper\FileUploadHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contacts")
 */
class ContactsController extends AbstractController
{
    /**
     * @Route("/", name="contacts_index")
     */
    public function index()
    {
        return $this->render(
            'contacts/index.html.twig',
            [
                'msg' => 'Hello, World!',
            ]
        );
    }

    /**
     * @Route("/add")
     */
    public function add(Request $request, FileUploadHelper $fileUploader)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the picture file must be processed only when a file is uploaded
            if ($pictureFile) {
                $fileUploader->upload($contact, $pictureFile);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('contacts_index'));
        }

        return $this->render(
            'contacts/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
