<?php

namespace Greg\Testing;

class Package
{
    protected $tester = null;

    public function run(Tester $tester)
    {
        $this->tester($tester);

        foreach(get_class_methods($this) as $method) {
            if (substr($method, 0, 4) == 'test') {
                $this->$method($tester);
            }
        }

        return $this;
    }

    /**
     * @param Tester $tester
     * @return $this|Tester|null
     */
    public function tester(Tester $tester = null)
    {
        if (func_num_args()) {
            $this->tester = $tester;

            return $this;
        }

        return $this->tester;
    }
}