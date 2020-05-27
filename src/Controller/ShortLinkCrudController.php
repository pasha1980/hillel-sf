<?php


namespace App\Controller;


use App\Entity\ShortLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShortLinkCrudController extends AbstractController
{
    /**
     * @Route(path="/short-links-list", name="short_links_list", methods={"GET"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $shortLinkRepository = $em->getRepository(ShortLink::class);

        $shortLinks = $shortLinkRepository->findAll();

        return $this->render('short-link/index.html.twig', ['shortLinks' => $shortLinks]);
    }

    /**
     * @Route(path="/short-link/new", methods={"GET","POST"})
     */
    public function create(Request $request)
    {
        $shortCode = $this ->createShortLink(5);

        $formBuilder = $this->createFormBuilder()
            ->add('fullUrl', TextType::class)
            ->add('shortCode', TextType::class, [
                'data' => $shortCode,
                'disabled' => true,
            ])
            ->add('code', HiddenType::class, ['data' => $shortCode])
            ->add('save', SubmitType::class, ['label' => 'Create Short Link']);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $shortLink = new ShortLink();
            $shortLink->setFullUrl($data['fullUrl']);
            $shortLink->setShortCode($data['code']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($shortLink);
            $em->flush();
            return $this->redirectToRoute('short_links_list');
        }

        return $this->render('short-link/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function createShortLink($lenght) :string
    {
        $string = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shortURL = '';
        for ($i=0; $i<$lenght; $i++){
            $letter = substr($string, (rand(1, iconv_strlen($string))), 1);
            $shortURL = $shortURL . $letter;
        }

        $verify = $this->getDoctrine()
            ->getManager()
            ->getRepository(ShortLink::class)
            ->findBy(['shortCode' => $shortURL]);

        if (isset($verify[0])){
            $this->createShortLink($lenght);
        }

        return $shortURL;
    }
}