<?php
declare(strict_types=1);
/**
 * /tests/Integration/Resource/UserResourceTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\Resource;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Resource\UserResource;
use App\Rest\RepositoryInterface;
use App\Rest\ResourceInterface;
use App\Security\Roles;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserResourceTest
 *
 * @package App\Tests\Integration\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserResourceTest extends ResourceTestCase
{
    protected $entityClass = User::class;
    protected $resourceClass = UserResource::class;
    protected $repositoryClass = UserRepository::class;

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @param RepositoryInterface $repository
     * @param ValidatorInterface  $validator
     *
     * @return ResourceInterface
     */
    protected function getResource(RepositoryInterface $repository, ValidatorInterface $validator): ResourceInterface
    {
        $roles = $this->getMockBuilder(Roles::class)->disableOriginalConstructor()->getMock();

        return new $this->resourceClass($repository, $validator, $roles);
    }
}
