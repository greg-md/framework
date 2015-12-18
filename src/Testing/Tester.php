<?php
namespace Greg\Testing;

class Tester
{
    protected $tests = [];

    public function it($condition, $function = null)
    {
        $test = new Test($condition);

        call_user_func_array($function, [$test]);

        $this->tests[] = $test;

        return $this;
    }

    public function run($package = null)
    {
        if (is_array($package)) {
            foreach($package as $item) {
                if (!($item instanceof Package)) {
                    throw new \Exception('Package should be an instance of tester package.');
                }

                $item->run($this);
            }
        } else {
            if (!($package instanceof Package)) {
                throw new \Exception('Package should be an instance of tester package.');
            }

            $package->run($this);
        }

        return $this;
    }

    public function getTests()
    {
        return $this->tests;
    }

    public function getFailedTests()
    {
        $tests = [];

        foreach($this->tests as $test) {
            if (($test instanceof Test) and $test->failed()) {
                $tests[] = $test;
            }
        }

        return $tests;
    }
}
