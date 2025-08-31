<?php
declare(strict_types=1);

namespace LatteView\TestApp\Form;

use Cake\Form\Form;
use Cake\Form\Schema;

class TestForm extends Form
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('name', 'string')
            ->addField('email', ['type' => 'string'])
            ->addField('body', ['type' => 'text']);
    }
}
