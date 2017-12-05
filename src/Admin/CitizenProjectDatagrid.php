<?php

namespace AppBundle\Admin;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\CitizenProject;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CitizenProjectDatagrid implements DatagridInterface
{
    use DatagridDecoratorTrait;

    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, CitizenProjectManager $manager)
    {
        $this->setDecorated($decorated);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        if (!$this->cachedResults) {
            $results = $this->decorated->getResults();

            /* @var CitizenProject[] $results */
            $this->manager->injectCitizenProjectAdministrators($results);
            $this->manager->injectCitizenProjectCreator($results);

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}
