<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Service\Helper\FileUploadHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contacts")
 */
class ContactsController extends AbstractController
{
    private $contactRepository;
    private $uploadPath;

    public function __construct(ContactRepository $contactRepository, string $uploadPath)
    {
        $this->contactRepository = $contactRepository;
        $this->uploadPath = $uploadPath;
    }

    /**
     * @Route("/", name="contacts_index")
     */
    public function index()
    {
        return $this->render(
            'contacts/index.html.twig', [
                // TODO: Change this according to requirements
                'contacts' => $this->contactRepository->findAll(),
            ]
        );
    }

    private function persistContact(
        FormInterface $form,
        Contact $contact,
        FileUploadHelper $fileUploader,
        string $existingPicture = null
    ): void {
        /** @var UploadedFile $pictureFile */
        $pictureFile = $form->get('picture')->getData();

        // this condition is needed because the 'picture' field is not required
        // so the picture file must be processed only when a file is uploaded
        if ($pictureFile) {
            // delete existing picture
            if ($existingPicture && file_exists($this->uploadPath . $existingPicture)) {
                unlink($this->uploadPath . $existingPicture);
            }

            $fileUploader->upload($contact, $pictureFile);
        } else {
            $contact->setPicture($existingPicture);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($contact);
        $entityManager->flush();
    }

    /**
     * @Route("/add", name="contacts_add")
     */
    public function add(Request $request, FileUploadHelper $fileUploader)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistContact($form, $contact, $fileUploader);

            return $this->redirect($this->generateUrl('contacts_index'));
        }

        return $this->render(
            'contacts/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="contacts_edit")
     */
    public function edit(Request $request, int $id, FileUploadHelper $fileUploader)
    {
        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw $this->createNotFoundException(sprintf('Not not found for given ID: %d', $id));
        }

        $existingPicture = $contact->getPicture();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistContact($form, $contact, $fileUploader, $existingPicture);

            return $this->redirect($this->generateUrl('contacts_index'));
        }

        return $this->render(
            'contacts/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="contacts_delete")
     */
    public function delete(int $id): Response
    {
        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw $this->createNotFoundException(sprintf('Not not found for given ID: %d', $id));
        }

        if ($contact->getPicture() && file_exists($this->uploadPath . $contact->getPicture())) {
            unlink($this->uploadPath . $contact->getPicture());
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($contact);
        $manager->flush();

        return $this->redirect($this->generateUrl('contacts_index'));
    }
}
