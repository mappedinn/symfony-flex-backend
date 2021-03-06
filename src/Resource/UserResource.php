<?php
declare(strict_types=1);
/**
 * /src/Resource/UserResource.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Resource;

use App\Entity\EntityInterface;
use App\Entity\User as Entity;
use App\Entity\UserGroup;
use App\Repository\UserRepository as Repository;
use App\Rest\DTO\RestDtoInterface;
use App\Rest\DTO\User;
use App\Rest\Resource;
use App\Security\Roles;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @noinspection PhpHierarchyChecksInspection */
/** @noinspection PhpMissingParentCallCommonInspection */

/**
 * Class UserResource
 *
 * @package App\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 *
 * @method Repository  getRepository(): Repository
 * @method Entity[]    find(array $criteria = null, array $orderBy = null, int $limit = null, int $offset = null, array $search = null): array
 * @method Entity|null findOne(string $id, bool $throwExceptionIfNotFound = null): ?EntityInterface
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null, bool $throwExceptionIfNotFound = null): ?EntityInterface
 * @method Entity      create(RestDtoInterface $dto): EntityInterface
 * @method Entity      update(string $id, RestDtoInterface $dto): EntityInterface
 * @method Entity      delete(string $id): EntityInterface
 * @method Entity      save(EntityInterface $entity, bool $skipValidation = null): EntityInterface
 */
class UserResource extends Resource
{
    /**
     * @var Roles
     */
    private $roles;

    /**
     * Class constructor.
     *
     * @param Repository         $repository
     * @param ValidatorInterface $validator
     * @param Roles              $roles
     */
    public function __construct(Repository $repository, ValidatorInterface $validator, Roles $roles)
    {
        $this->setRepository($repository);
        $this->setValidator($validator);

        $this->roles = $roles;

        $this->setDtoClass(User::class);
    }

    /**
     * Method to fetch users for specified user group, note that this method will also check user role inheritance so
     * return value will contain all users that belong to specified user group via role inheritance.
     *
     * @param UserGroup $userGroup
     *
     * @return Entity[]
     */
    public function getUsersForGroup(UserGroup $userGroup): array
    {
        /**
         * Filter method to see if specified user belongs to certain user group.
         *
         * @param Entity $user
         *
         * @return bool
         */
        $filter = function (Entity $user) use ($userGroup): bool {
            $user->setRolesService($this->roles);

            return \in_array($userGroup->getRole()->getId(), $user->getRoles(), true);
        };

        return \array_values(\array_filter($this->find(), $filter));
    }
}
