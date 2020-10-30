<?php

namespace App;

use App\Exceptions\FiledNotFound;
use App\Exceptions\NoQueryValidFound;
use App\Exceptions\SyntaxError;

/**
 * Class QueryParser
 * @package App
 */
final class QueryParser
{
    private  $mapping;

    private  $query;

    private $kql = '';

    private $lastOperator = '';

    const Greater = '>';
    const Smaller = '<';
    const EQGreater = '>=';
    const EQSmaller = '<=';
    const Eq = '=';
    const NoEq = '!=';

    const Operations = [self::Greater, self::Eq, self::EQGreater, self::Smaller, self::EQSmaller, self::NoEq];


    /**
     * QueryParser constructor.
     * @param MappingInterface $mapping
     * @param $query
     * @throws NoQueryValidFound
     */
    public function __construct(MappingInterface $mapping,  $query)
    {
        $this->mapping = $mapping;

        if (empty($query)) {
            throw new NoQueryValidFound( $query);
        }
        // case insensitive
        $this->query = strtolower($query);
    }

    /**
     * @return $this
     * @throws FiledNotFound
     * @throws SyntaxError
     */
    public function parse()
    {
        $subQueries = $this->subQueries();
        foreach ($subQueries as $query) {
            $this->checkSyntax($query);
        }

        return $this;
    }

    /**
     * @param $query
     * @throws FiledNotFound
     * @throws SyntaxError
     */
    private function checkSyntax($query)
    {
        $counter = 0;
        $op = '';
        foreach (self::Operations as $operation) {
            if (strpos($query['item'], $operation) !== false) {
                $counter++;
                //only one operation
                if ($counter > 1) {
                    throw new SyntaxError(' no operation found in multi level query');
                }
                $op = $operation;
            }
        }

        if (empty($op)) {
            throw new SyntaxError(' no operation found in multi level query');
        }

        $newQueries = explode($op, $query['item']);

        for ($i=0; $i < count($newQueries); $i=$i+2) {
                $this->checkField($newQueries[$i]);
                $fields = explode('.', trim($newQueries[$i]));
                $field = $fields[0];
                if (count($fields) > 1) {
                    $field = $fields[0];
                    $end = end($fields);
                    for ($j =1; $j < count($fields)-1; $j++) {
                        $field .= '.' . $fields[$j];
                    }
                    $this->addNestedKQL($field, $end, $op, $query['operation'], trim($newQueries[$i+1]));
                } else {
                    $this->addKQL($field, $op, $query['operation'], trim($newQueries[$i+1]));
                }
        }
    }

    /**
     * @param $query
     * @throws FiledNotFound
     */
    private function checkField($query)
    {
        $fields = explode('.', trim($query));
        $mapping = $this->mapping->toArray();
        $mapping = array_change_key_case($mapping);

        for ($i=0; $i < count($fields); $i++) {
            if (!isset($mapping[$fields[$i]])) {
                throw new FiledNotFound($fields[$i]);
            }
            if (is_array($mapping[$fields[$i]])) {
                $mapping = $mapping[$fields[$i]];
            }
            $mapping = array_change_key_case($mapping);
        }
    }

    /**
     * @return array
     */
    private function subQueries()
    {
        return array_values($this->ChunkByyBooleanOperations());
    }

    /**
     * @param $field
     * @param $op
     * @param $operation
     * @param $val
     */
    private function addKQL($field, $op, $operation, $val)
    {
        if (empty($this->kql)) {
            $this->kql .= ' ' . $field . ': ' . $op . ' ' . $val . ' ';
        } else {
            $this->kql .= $this->lastOperator . ' ' . $field . ': ' . $op . ' ' . $val;
        }
        $this->lastOperator = $operation;
    }

    /**
     * @return array
     */
    private function ChunkByyBooleanOperations()
    {
        $result =  explode( ' and ', $this->query);
        $operator = ' or ';
        $otherOperator = ' and ';

        if (count($result) == 1)  {
            $result =  explode( ' or ', $this->query);
            $operator = ' and ';
            $otherOperator = ' or ';
        }

        if (count($result) == 1)  {
            return [['operation' => $operator, 'item' => $result[0]]];
        }

        foreach ($result as $index => $subQuery) {
            if (strpos($subQuery, $operator) !==  false) {
                unset($result[$index]);
                $or = explode( $operator, $subQuery);
                foreach ($or as $item) {
                    $result[] = ['operation' => $operator, 'item' =>$item];
                }

            } else {
                $result[$index] = ['operation' => $otherOperator, 'item' => $result[$index]];
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getKQL()
    {
        return " { " . $this->kql . " } ";
    }

    /**
     * @param $field
     * @param $end
     * @param $op
     * @param $operation
     * @param $value
     */
    private function addNestedKQL($field, $end, $op, $operation, $value)
    {
        if (empty($this->kql)) {
            $this->kql .=  ' ' .$field . ': { ' . $end . ' ' . $op . ' ' . $value . ' }   ';
        } else {
            $this->kql .= $this->lastOperator  . ' ' .$field . ': { ' . $end . ' ' . $op . ' ' . $value . ' }   ';
        }
        $this->lastOperator = $operation;
    }
}