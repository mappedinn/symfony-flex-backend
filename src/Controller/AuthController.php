<?php
declare(strict_types=1);
/**
 * /src/Controller/AuthController.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Controller;

use App\Entity\User;
use App\Security\Roles;
use App\Utils\JSON;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AuthController
 *
 * @Route(
 *      path="/auth",
 *  )
 *
 * @package App\Controller
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class AuthController
{
    /**
     * Endpoint action to get user Json Web Token (JWT) for authentication.
     *
     * @Route("/getToken");
     *
     * @Method("POST")
     *
     * @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      description="Credentials object",
     *      required=true,
     *      @SWG\Schema(
     *          example={"username": "username", "password": "password"}
     *      )
     *  )
     * @SWG\Response(
     *      response=200,
     *      description="JSON Web Token for user",
     *  )
     * @SWG\Response(
     *      response=400,
     *      description="Invalid body content",
     *  )
     * @SWG\Response(
     *      response=401,
     *      description="Bad credentials",
     *  )
     * @SWG\Tag(name="Authentication")
     *
     * @throws \LogicException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getTokenAction(): void
    {
        $message = \sprintf(
            'You need to send JSON body to obtain token eg. %s',
            JSON::encode(['username' => 'username', 'password' => 'password'])
        );

        throw new HttpException(400, $message);
    }

    /**
     * Endpoint action to get current user profile data.
     *
     * @Route("/profile");
     *
     * @Method("GET")
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @SWG\Parameter(
     *      type="string",
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      description="Authorization header",
     *      default="Bearer _your_jwt_here_",
     *  )
     * @SWG\Response(
     *      response=200,
     *      description="User profile data",
     *      @SWG\Schema(
     *          @Model(
     *              type=User::class,
     *              groups={"User", "User.userGroups", "User.roles", "UserGroup", "UserGroup.role"},
     *          ),
     *      ),
     *  )
     * @SWG\Response(
     *      response=401,
     *      description="Invalid token",
     *      examples={
     *          "Token not found": "{code: 401, message: 'JWT Token not found'}",
     *          "Expired token": "{code: 401, message: 'Expired JWT Token'}",
     *      },
     *  )
     * @SWG\Tag(name="Authentication")
     *
     * @param UserInterface|User  $user
     * @param Roles               $roles
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function profileAction(
        UserInterface $user,
        Roles $roles,
        SerializerInterface $serializer
    ): JsonResponse
    {
        // Specify used serialization groups
        static $groups = [
            'User',
            'User.userGroups',
            'User.roles',
            'UserGroup',
            'UserGroup.role',
        ];

        // Set roles service to user entity, so we can get inherited roles
        $user->setRolesService($roles);

        // Create response
        return new JsonResponse(
            $serializer->serialize($user, 'json', ['groups' => $groups]),
            200,
            [],
            true
        );
    }
}
