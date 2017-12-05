<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\EntityPostAddressTrait;
use AppBundle\Entity\EventCategory;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Search\SearchParametersFilter;
use AppBundle\Search\SearchResultsProvidersManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/evenements", name="app_search_events")
     * @Method("GET")
     */
    public function searchEventsAction(Request $request)
    {
        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);

        $search = $this->getSearch($request);
        $user = $this->getUser();
        if ($user && in_array(EntityPostAddressTrait::class, class_uses($user))) {
            $search->setCity(sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        }

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_events.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_EVENTS,
            'event_categories' => $this->getDoctrine()->getRepository(EventCategory::class)->findAllEnabledOrderedByName(),
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/comites", name="app_search_committees")
     * @Method("GET")
     */
    public function searchCommitteesAction(Request $request)
    {
        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_COMMITTEES);

        $search = $this->getSearch($request);

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_committees.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_COMMITTEES,
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/projets-citoyens", name="app_search_citizen_projects")
     * @Method("GET")
     */
    public function searchCitizenProjectsAction(Request $request)
    {
        $this->disableInProduction();

        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_CITIZEN_PROJECTS);

        $search = $this->getSearch($request);

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_citizen_projects.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_CITIZEN_PROJECTS,
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/recherche", name="app_search")
     * @Method("GET")
     */
    public function resultsAction(Request $request)
    {
        $search = $this->getSearch($request);

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/results.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    private function getSearch(Request $request): SearchParametersFilter
    {
        return $this
            ->get('app.search.search_results_filter')
            ->setMaxResults($this->getParameter('search_max_results'))
            ->handleRequest($request);
    }
}
