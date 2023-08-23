<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
use PhpParser\Node\Expr\FuncCall;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            self::assertEquals($key + 1, $value);
        }
    }

    public function testForCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());
        // pop
        $result = $collection->pop();
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
        $this->assertEquals(3, $result);
    }

    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(["Nozami"]);
        $result = $collection->mapInto(Person::class);

        $this->assertEquals([new Person("Nozami")], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            ["Fardan", "Nozami"],
            ["Nozami", "Ajitama"]
        ]);
        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . " " . $lastName;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Fardan Nozami"),
            new Person("Nozami Ajitama"),
        ], $result->all());
    }

    public function testMapToGroups()
    {
        $collection = collect([
            [
                "name" => "Fardan",
                "Department" => "IT",
            ],
            [
                "name" => "Nozami",
                "Department" => "HR",
            ],
            [
                "name" => "Ajitama",
                "Department" => "IT",
            ],
        ]);
        $result = $collection->mapToGroups(function ($person) {
            return [$person["Department"] => $person["name"]];
        });

        $this->assertEquals([
            "IT" => collect(["Fardan", "Ajitama"]),
            "HR" => collect(["Nozami"])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEqualsCanonicalizing(
            [1, 2, 3, 4, 5, 6],
            $collection3->all()
        );
    }

    public function testCombine()
    {
        $collection1 = collect(["name", "Country"]);
        $collection2 = collect(["Nozami", "Indonesia"]);
        $collection3 = $collection1->combine($collection2);

        $this->assertEqualsCanonicalizing(
            [
                "name" => "Nozami",
                "country" => "Indonesia"
            ],
            $collection3->all()
        );
    }

    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);
        $result = $collection->collapse();

        $this->assertEqualsCanonicalizing(
            [
                1, 2, 3, 4, 5, 6, 7, 8, 9
            ],
            $result->all()
        );
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Nozami",
                "hobbies" => ["gaming", "coding"]
            ],
            [
                "name" => "Ajitama",
                "hobbies" => ["traveling", "hiking"]
            ],
        ]);
        $result = $collection->flatMap(function ($item) {
            return $item["hobbies"];
        });

        $this->assertEqualsCanonicalizing(
            [
                "gaming", "coding", "traveling", "hiking"
            ],
            $result->all()
        );
    }

    public function testJoin(): void
    {
        $collection = collect(["Fardan", "Nozami", "Ajitama", "Wildan"]);

        $this->assertEquals("Fardan Nozami Ajitama Wildan", $collection->join(" "));
        $this->assertEquals("Fardan-Nozami-Ajitama-Wildan", $collection->join("-"));
        $this->assertEquals("Fardan-Nozami-Ajitama_Wildan", $collection->join("-", "_"));
        $this->assertEquals("Fardan, Nozami, Ajitama and Wildan", $collection->join(", ", " and "));
    }

    public function testFilter(): void
    {
        $collection = collect([
            "fardan" => 100,
            "nozami" => 80,
            "Ajitama" => 70
        ]);

        $result = $collection->filter(fn ($value, $key) => $value >= 80);

        $this->assertEquals([
            "fardan" => 100,
            "nozami" => 80
        ], $result->all());
    }

    public function testFilterIndex(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->filter(fn ($value, $key) => $value % 2 == 0);

        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }

    public function testPartition(): void
    {
        $collection = collect([
            "fardan" => 100,
            "nozami" => 80,
            "Ajitama" => 70
        ]);

        [$result1, $result2] = $collection->partition(fn ($value, $key) => $value >= 80);

        $this->assertEquals([
            "fardan" => 100,
            "nozami" => 80,
        ], $result1->all());
        $this->assertEquals([
            "Ajitama" => 70
        ], $result2->all());
    }

    public function testTesting(): void
    {
        $collection = collect(["Fardan", "Nozami", "Ajitama"]);
        $this->assertTrue($collection->contains("Ajitama"));
        $this->assertTrue($collection->contains(fn ($value, $key) => $value == "Nozami"));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "Nozami",
                "department" => "IT"
            ],
            [
                "name" => "Fardan",
                "department" => "IT"
            ],
            [
                "name" => "Ajitama",
                "department" => "HR"
            ],

        ]);

        $result = $collection->groupBy("department");

        $this->assertEquals(
            [
                "IT" => collect([
                    [
                        "name" => "Nozami",
                        "department" => "IT"
                    ],
                    [
                        "name" => "Fardan",
                        "department" => "IT"
                    ],
                ]),
                "HR" => collect([
                    [
                        "name" => "Ajitama",
                        "department" => "HR"
                    ],
                ])
            ],
            $result->all()
        );
        $result = $collection->groupBy(fn ($value, $key) => strtolower($value["department"]));

        $this->assertEquals([
            "it" => collect([
                [
                    "name" => "Nozami",
                    "department" => "IT"
                ],
                [
                    "name" => "Fardan",
                    "department" => "IT"
                ],
            ]),
            "hr" => collect([
                [
                    "name" => "Ajitama",
                    "department" => "HR"
                ],
            ])
        ], $result->all());
    }

    public function testSlice()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4, 5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(fn ($value, $key) => $value == 3);
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(fn ($value, $key) => $value < 3);
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(fn ($value, $key) => $value == 3);
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(fn ($value, $key) => $value < 3);
        $this->assertEqualsCanonicalizing([3 , 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testChunk()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4 , 5, 6, 7, 8, 9]);

        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(fn ($value, $key) => $value > 5);
        $this->assertEquals(6, $result);
    }

    public function testLast()
    {
        $collection = collect([1, 2, 3, 4 , 5, 6, 7, 8, 9]);

        $result = $collection->last();
        $this->assertEquals(9, $result);

        $result = $collection->last(fn ($value, $key) => $value < 5);
        $this->assertEquals(4, $result);
    }

    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->random();
        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }

    public function testCheckingExistance()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->isEmpty(10));
        $this->assertTrue($collection->contains(fn ($value, $key) => $value == 8));
    }

    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 5, 4, 6, 7, 8, 9]);
        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());
    }

    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->sum();
        $this->assertEquals(45, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);
        $result = $collection->max();
        $this->assertEquals(9, $result);
    }

    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->reduce(fn ($carry, $item) => $carry + $item);
        $this->assertEquals(45, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make( function () {
            $value = 0;
            while (true) {
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(10);
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
}
