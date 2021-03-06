<?php
declare(strict_types=1);
/**
 * /src/Rest/RequestHandler.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Rest;

use App\Utils\JSON;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class RequestHandler
 *
 * @package App\Rest
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
final class RequestHandler
{
    /**
     * Method to get used criteria array for 'find' and 'count' methods. Some examples below.
     *
     * Basic usage:
     *  ?where={"foo": "bar"}                       => WHERE entity.foo = 'bar'
     *  ?where={"bar.foo": "foobar"}                => WHERE bar.foo = 'foobar'
     *  ?where={"id": [1,2,3]}                      => WHERE entity.id IN (1,2,3)
     *  ?where={"bar.foo": [1,2,3]}                 => WHERE bar.foo IN (1,2,3)
     *
     * Advanced usage:
     *  By default you cannot make anything else that described above, but you can easily manage special cases within
     *  your controller 'processCriteria' method, where you can modify this generated 'criteria' array as you like.
     *
     *  Note that with advanced usage you can easily use everything that App\Repository\Base::getExpression method
     *  supports - and that is basically 99% that you need on advanced search criteria.
     *
     * TODO create an example of advanced usage.
     *
     * @param HttpFoundationRequest $request
     *
     * @return array
     *
     * @throws HttpException
     */
    public static function getCriteria(HttpFoundationRequest $request): array
    {
        try {
            $where = \array_filter(JSON::decode($request->get('where', '{}'), true));
        } catch (\LogicException $error) {
            throw new HttpException(
                HttpFoundationResponse::HTTP_BAD_REQUEST,
                'Current \'where\' parameter is not valid JSON.'
            );
        }

        return $where;
    }

    /**
     * Getter method for used order by option within 'find' method. Some examples below.
     *
     * Basic usage:
     *  ?order=column1                                  => ORDER BY entity.column1 ASC
     *  ?order=-column1                                 => ORDER BY entity.column2 DESC
     *  ?order=foo.column1                              => ORDER BY foo.column1 ASC
     *  ?order=-foo.column1                             => ORDER BY foo.column2 DESC
     *
     * Array parameter usage:
     *  ?order[column1]=ASC                             => ORDER BY entity.column1 ASC
     *  ?order[column1]=DESC                            => ORDER BY entity.column1 DESC
     *  ?order[column1]=foobar                          => ORDER BY entity.column1 ASC
     *  ?order[column1]=DESC&order[column2]=DESC        => ORDER BY entity.column1 DESC, entity.column2 DESC
     *  ?order[foo.column1]=ASC                         => ORDER BY foo.column1 ASC
     *  ?order[foo.column1]=DESC                        => ORDER BY foo.column1 DESC
     *  ?order[foo.column1]=foobar                      => ORDER BY foo.column1 ASC
     *  ?order[foo.column1]=DESC&order[column2]=DESC    => ORDER BY foo.column1 DESC, entity.column2 DESC
     *
     * @param HttpFoundationRequest $request
     *
     * @return array
     */
    public static function getOrderBy(HttpFoundationRequest $request): array
    {
        // Normalize parameter value
        $input = \array_filter((array)$request->get('order', []));

        // Initialize output
        $output = [];

        /**
         * Lambda function to process user input for 'order' parameter and convert it to proper array that
         * Doctrine repository find method can use.
         *
         * @param   string          $value
         * @param   integer|string  $key
         */
        $iterator = function (string &$value, $key) use (&$output) {
            $order = 'ASC';

            if (\is_string($key)) {
                $column = $key;
                $order = \in_array(mb_strtoupper($value), ['ASC', 'DESC'], true) ? mb_strtoupper($value) : $order;
            } else {
                $column = $value;
            }

            if ($column[0] === '-') {
                $column = mb_substr($column, 1);
                $order = 'DESC';
            }

            $output[$column] = $order;
        };

        // Process user input
        \array_walk($input, $iterator);

        return $output;
    }

    /**
     * Getter method for used limit option within 'find' method.
     *
     * Usage:
     *  ?limit=10
     *
     * @param HttpFoundationRequest $request
     *
     * @return null|integer
     */
    public static function getLimit(HttpFoundationRequest $request): ?int
    {
        $limit = $request->get('limit');

        return $limit === null ? null : (int)\abs($limit);
    }

    /**
     * Getter method for used offset option within 'find' method.
     *
     * Usage:
     *  ?offset=10
     *
     * @param HttpFoundationRequest $request
     *
     * @return null|integer
     */
    public static function getOffset(HttpFoundationRequest $request): ?int
    {
        $offset = $request->get('offset');

        return $offset === null ? null : (int)\abs($offset);
    }

    /**
     * Getter method for used search terms within 'find' and 'count' methods. Note that these will affect to columns /
     * properties that you have specified to your resource service repository class.
     *
     * Usage examples:
     *  ?search=term
     *  ?search=term1+term2
     *  ?search={"and": ["term1", "term2"]}
     *  ?search={"or": ["term1", "term2"]}
     *  ?search={"and": ["term1", "term2"], "or": ["term3", "term4"]}
     *
     * @throws HttpException
     *
     * @param HttpFoundationRequest $request
     *
     * @return array
     */
    public static function getSearchTerms(HttpFoundationRequest $request): array
    {
        $search = $request->get('search');
        $output = [];

        if ($search !== null) {
            $input =null;

            try {
                $input = JSON::decode($search, true);

                if (!\is_array($input)) {
                    throw new \LogicException('Search term is not an array, fallback to string handling');
                }

                if (!\array_key_exists('and', $input) && !\array_key_exists('or', $input)) {
                    throw new HttpException(
                        HttpFoundationResponse::HTTP_BAD_REQUEST,
                        'Given search parameter is not valid, within JSON provide \'and\' and/or \'or\' property.'
                    );
                }
            } catch (\LogicException $error) { // Parameter was not JSON so just use parameter values as search strings
                // By default we want to use 'OR' operand with given search words.
                $output = [
                    'or' => \array_unique(\array_values(\array_filter(\explode(' ', $search))))
                ];

                $input = null;
            }

            if ($input !== null) {
                /**
                 * Lambda function to normalize JSON search terms.
                 *
                 * @param   string|array $terms
                 */
                $iterator = function (&$terms) {
                    $terms = \array_unique(\array_values(\array_filter($terms)));
                };

                // Normalize user input, note that this support array and string formats on value
                \array_walk($input, $iterator);

                $output = $input;
            }
        }

        return $output;
    }
}
