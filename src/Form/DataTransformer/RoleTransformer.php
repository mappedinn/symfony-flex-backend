<?php
declare(strict_types=1);
/**
 * /src/Form/DataTransformer/RoleTransformer.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Form\DataTransformer;

use App\Entity\Role;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class RoleTransformer
 *
 * @package App\Form\Console\DataTransformer
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class RoleTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * RoleTransformer constructor.
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (Role) to a string (Role id).
     *
     * @param Role|null $role
     *
     * @return string
     */
    public function transform($role): string
    {
        return ($role instanceof Role) ? $role->getId() : '';
    }

    /**
     * Transforms a string (Role id) to an object (Role).
     *
     * @param  string $roleName
     *
     * @return Role|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($roleName): ?Role
    {
        if ($roleName !== null) {
            $role = $this->manager
                ->getRepository(Role::class)
                ->find($roleName);

            if ($role === null) {
                throw new TransformationFailedException(\sprintf(
                    'Role with name "%s" does not exist!',
                    $roleName
                ));
            }
        }

        return $role ?? null;
    }
}
