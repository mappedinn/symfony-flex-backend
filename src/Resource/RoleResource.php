<?php
declare(strict_types=1);
/**
 * /src/Resource/RoleResource.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Resource;

use App\Entity\EntityInterface;
use App\Entity\Role as Entity;
use App\Repository\RoleRepository as Repository;
use App\Rest\DTO\RestDtoInterface;
use App\Rest\Resource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @noinspection PhpHierarchyChecksInspection */
/** @noinspection PhpMissingParentCallCommonInspection */

/**
 * Class RoleResource
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
class RoleResource extends Resource
{
    /**
     * Class constructor.
     *
     * @param Repository         $repository
     * @param ValidatorInterface $validator
     */
    public function __construct(Repository $repository, ValidatorInterface $validator)
    {
        $this->setRepository($repository);
        $this->setValidator($validator);
    }
}
