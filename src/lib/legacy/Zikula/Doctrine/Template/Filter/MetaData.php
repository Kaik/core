<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doctrine filter for the Attributable doctrine template.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Filter_MetaData extends Doctrine_Record_Filter
{
    /**
     * Filters write access to the unknown property __META__.
     *
     * @param Doctrine_Record $record Record
     * @param string          $name   Name of the unkown property
     * @param mixed           $value  Value of to set
     *
     * @return void
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __META__
     */
    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if ('__META__' == $name) {
            $record->mapValue('__META__', new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS));
            if (Doctrine_Record::STATE_CLEAN == $record->state()) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }
        }

        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }

    /**
     * Filters read access to the unknown property __META__.
     *
     * @param Doctrine_Record $record Record
     * @param string          $name   Name of the unkown property
     *
     * @return mixed
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __META__
     */
    public function filterGet(Doctrine_Record $record, $name)
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if ('__META__' == $name) {
            $value = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

            $record->mapValue('__META__', $value);
            if (Doctrine_Record::STATE_CLEAN == $record->state()) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }

            return $value;
        }

        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }
}
