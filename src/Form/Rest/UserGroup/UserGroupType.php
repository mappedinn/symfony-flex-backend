<?php
declare(strict_types=1);
/**
 * /src/Form/Rest/UserGroup/UserGroupType.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Form\Rest\UserGroup;

use App\Entity\Role as RoleEntity;
use App\Form\DataTransformer\RoleTransformer;
use App\Repository\RoleRepository;
use App\Rest\DTO\UserGroup as UserGroupDto;
use App\Security\Roles;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserGroupType
 *
 * @package App\Form\Rest\UserGroup
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserGroupType extends AbstractType
{
    /**
     * @var Roles
     */
    private $roles;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * UserGroupType constructor.
     *
     * @param Roles          $roles
     * @param RoleRepository $roleRepository
     * @param ObjectManager  $objectManager
     */
    public function __construct(
        Roles $roles,
        RoleRepository $roleRepository,
        ObjectManager $objectManager
    )
    {
        $this->roles = $roles;
        $this->roleRepository = $roleRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                Type\TextType::class,
                [
                    'label'         => 'Group name',
                    'required'      => true,
                    'empty_data'    => '',
                ]
            )
            ->add(
                'role',
                Type\ChoiceType::class,
                [
                    'label'         => 'Role',
                    'choices'       => $this->getRoleChoices(),
                    'required'      => true,
                ]
            );

        $builder->get('role')->addModelTransformer(new RoleTransformer($this->objectManager));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserGroupDto::class,
        ]);
    }

    /**
     * Method to  choices array for user groups.
     *
     * @return  array
     */
    public function getRoleChoices(): array
    {
        // Initialize output
        $choices = [];

        $helper = $this->roles;

        $iterator = function (RoleEntity $role) use (&$choices, $helper) {
            $name = $helper->getRoleLabel($role->getId());

            $choices[$name] = $role->getId();
        };

        \array_map($iterator, $this->roleRepository->findAll());

        return $choices;
    }
}
