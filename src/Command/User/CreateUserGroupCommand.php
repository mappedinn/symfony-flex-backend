<?php
declare(strict_types=1);
/**
 * /src/Command/User/CreateUserGroupCommand.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Command\User;

use App\Form\Console\UserGroupType;
use App\Repository\RoleRepository;
use App\Resource\UserGroupResource;
use App\Rest\DTO\UserGroup as UserGroupDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateUserGroupCommand
 *
 * @package App\Command\User
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class CreateUserGroupCommand extends Command
{
    /**
     * @var array
     */
    private static $commandParameters = [
        [
            'name'          => 'name',
            'description'   => 'Name of the user group',
        ],
        [
            'name'          => 'role',
            'description'   => 'Role of the user group',
        ],
    ];

    /**
     * @var UserGroupResource
     */
    private $userGroupResource;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * CreateUserGroupCommand constructor.
     *
     * @param null              $name
     * @param UserGroupResource $userGroupResource
     * @param RoleRepository    $roleRepository
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(
        $name = null,
        UserGroupResource $userGroupResource,
        RoleRepository $roleRepository
    )
    {
        parent::__construct('user:create-group');

        $this->userGroupResource = $userGroupResource;
        $this->roleRepository = $roleRepository;

        $this->setDescription('Console command to create user groups');
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        /**
         * Lambda iterator function to parse specified inputs.
         *
         * @param array $input
         *
         * @return InputOption
         */
        $iterator = function (array $input) {
            return new InputOption(
                $input['name'],
                $input['shortcut']    ?? null,
                $input['mode']        ?? InputOption::VALUE_OPTIONAL,
                $input['description'] ?? '',
                $input['default']     ?? null
            );
        };

        // Configure command
        $this->setDefinition(new InputDefinition(\array_map($iterator, self::$commandParameters)));
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->write(\sprintf("\033\143"));

        // Check that roles exists
        $this->checkRoles($output, $input->isInteractive());

        /** @var UserGroupDto $dto */
        $dto = $this->getHelper('form')->interactUsingForm(
            UserGroupType::class,
            $input,
            $output
        );

        // Create new user group
        $this->userGroupResource->create($dto);

        if ($input->isInteractive()) {
            $this->io->success('User group created - have a nice day');
        }

        return null;
    }

    /**
     * Method to check if database contains role(s), if non exists method will run 'user:create-roles' command
     * which creates all roles to database so that user groups can be created.
     *
     * @param OutputInterface $output
     * @param bool            $interactive
     *
     * @throws \Exception
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    private function checkRoles(OutputInterface $output, bool $interactive): void
    {
        if ($this->roleRepository->countAdvanced() !== 0) {
            return;
        }

        if ($interactive) {
            $this->io->block([
                'Roles are not yet created, creating those now...'
            ]);
        }

        $command = $this->getApplication()->find('user:create-roles');

        $arguments = [
            'command' => 'user:create-roles',
        ];

        $input = new ArrayInput($arguments);

        $command->run($input, $output);
    }
}
