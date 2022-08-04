<?php

namespace PostHog\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use PostHog\FeatureFlag;
use PostHog\Client;
use PostHog\PostHog;
use PostHog\Test\Assets\MockedResponses;
use PostHog\InconclusiveMatchException;

class FeatureFlagMatch extends TestCase
{

    public function setUp(): void
    {
        date_default_timezone_set("UTC");
    }

    public function testMatchPropertyEquals(): void
    {   
        $prop = [
            "key" => "key",
            "value" => "value",
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => null,
        ]));
        
        self::expectException(InconclusiveMatchException::class);
        FeatureFlag::matchProperty($prop, [
            "key2" => "value2",
        ]);

        $prop = [
            "key" => "key",
            "value" => "value",
            "operator" => "exact"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        $prop = [
            "key" => "key",
            "value" => ["value1", "value2", "value3"],
            "operator" => "exact"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value1",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value3",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value4",
        ]));

        self::expectException(InconclusiveMatchException::class);
        FeatureFlag::matchProperty($prop, [
            "key2" => "value",
        ]);

    }

    public function testMatchPropertyNotIn(): void
    {   
        $prop = [
            "key" => "key",
            "value" => "value",
            "operator" => "is_not"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => null,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "",
        ]));

        $prop = [
            "key" => "key",
            "value" => ["value1", "value2", "value3"],
            "operator" => "is_not"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value4",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value5",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value6",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => null,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value3",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "value1",
        ]));

        self::expectException(InconclusiveMatchException::class);
        FeatureFlag::matchProperty($prop, [
            "key2" => "value",
        ]);

    }

    public function testMatchPropertyIsSet(): void
    {   
        $prop = [
            "key" => "key",
            "value" => "is_set",
            "operator" => "is_set"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => null,
        ]));

        self::expectException(InconclusiveMatchException::class);
        FeatureFlag::matchProperty($prop, [
            "key2" => "value",
        ]);
        
    }
    

    public function testMatchPropertyContains(): void
    {   
        $prop = [
            "key" => "key",
            "value" => "valUe",
            "operator" => "icontains"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value2",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value3",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "value4",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "343tfvalue5",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "Alakazam",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => 123,
        ]));

        $prop = [
            "key" => "key",
            "value" => 3,
            "operator" => "icontains"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "3",
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 323,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => "val3",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "three",
        ]));
    }

    // public function testMatchPropertyRegex(): void
    // {   
    //     $prop = [
    //         "key" => "key",
    //         "value" => "/.com/",
    //         "operator" => "regex"
    //     ];

    //     self::assertTrue(FeatureFlag::matchProperty($prop, [
    //         "key" => "value.com",
    //     ]));

    //     self::assertTrue(FeatureFlag::matchProperty($prop, [
    //         "key" => "value2.com",
    //     ]));

    //     self::assertTrue(FeatureFlag::matchProperty($prop, [
    //         "key" => ".com343tfvalue5",
    //     ]));

    //     self::assertFalse(FeatureFlag::matchProperty($prop, [
    //         "key" => "Alakazam",
    //     ]));

    //     self::assertFalse(FeatureFlag::matchProperty($prop, [
    //         "key" => 123,
    //     ]));

    // }

    public function testMatchPropertyMathOperators(): void
    {   
        $prop = [
            "key" => "key",
            "value" => 1,
            "operator" => "gt"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 2,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 3,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => 0,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => -1,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "23",
        ]));

        $prop = [
            "key" => "key",
            "value" => 1,
            "operator" => "lt"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 0,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => -1,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => -3,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => 1,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "1",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "3",
        ]));

        $prop = [
            "key" => "key",
            "value" => 1,
            "operator" => "gte"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 1,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 2,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => 0,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => -1,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "3",
        ]));

        $prop = [
            "key" => "key",
            "value" => 43,
            "operator" => "lte"
        ];

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 0,
        ]));

        self::assertTrue(FeatureFlag::matchProperty($prop, [
            "key" => 43,
        ]));


        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => 44,
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "1",
        ]));

        self::assertFalse(FeatureFlag::matchProperty($prop, [
            "key" => "3",
        ]));

    }

    public function testFlagPersonProperties()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertTrue(PostHog::getFeatureFlag('person-flag', 'some-distinct-id', False, [], ["region" => "USA"]));
        $this->assertFalse(PostHog::getFeatureFlag('person-flag', 'some-distinct-id-2', False, [], ["region" => "Canada"]));

    }

    public function testFlagGroupProperties()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_GROUP_PROPERTIES_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertFalse(PostHog::getFeatureFlag('group-flag', 'some-distinct-1', False, [], [], ["company" => ["name" => "Project Name 1"]]));
        $this->assertFalse(PostHog::getFeatureFlag('group-flag', 'some-distinct-2', False, [], [], ["company" => ["name" => "Project Name 2"]]));
        $this->assertTrue(PostHog::getFeatureFlag('group-flag', 'some-distinct-id', False, ["company" => "amazon_without_rollout"], [], ["company" => ["name" => "Project Name 1"]]));
        $this->assertFalse(PostHog::getFeatureFlag('group-flag', 'some-distinct-i', False, ["company" => "amazon"], [], ["company" => ["name" => "Project Name 1"]]));
        $this->assertFalse(PostHog::getFeatureFlag('group-flag', 'some-distinct-id', False, ["company" => "amazon_without_rollout"], [], ["company" => ["name" => "Project Name 2"]]));
        $this->assertEquals(PostHog::getFeatureFlag('group-flag', 'some-distinct-id', False, ["company" => "amazon"], [], ["company" => []]), 'decide-fallback-value');
    }

    public function testFlagComplexDefinition()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_COMPLEX_FLAG_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertTrue(PostHog::getFeatureFlag('complex-flag', 'some-distinct-id', False, [], ["region" => "USA", "name" => "Aloha"], []));
        $this->assertTrue(PostHog::getFeatureFlag('complex-flag', 'some-distinct-within-roll', False, [], ["region" => "USA", "email" => "a@b.com"], []));
        $this->assertEquals(PostHog::getFeatureFlag('complex-flag', 'some-distinct-within-rollout', False, [], ["region" => "USA", "email" => "a@b.com"], []), 'decide-fallback-value');
        $this->assertEquals(PostHog::getFeatureFlag('complex-flag', 'some-distinct-within-rollout', False, [], ["doesnt_matter" => "1"], []), 'decide-fallback-value');
        $this->assertEquals(PostHog::getFeatureFlag('complex-flag', 'some-distinct-id', False, [], ["region" => "USA"], []), 'decide-fallback-value');
        $this->assertFalse(PostHog::getFeatureFlag('complex-flag', 'some-distinct-within-rollout', False, [], ["region" => "USA", "email" => "a@b.com", "name" => "X", "doesnt_matter" => "1"], []));
    }

    public function testFlagFallbackToDecide()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::FALLBACK_TO_DECIDE_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertEquals(PostHog::getFeatureFlag('feature-1', 'some-distinct'), 'decide-fallback-value');
        $this->assertEquals(PostHog::getFeatureFlag('feature-2', 'some-distinct'), 'decide-fallback-value');
    }

    public function testFeatureFlagDefaultsDontHinderRegularEvaluation()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_SIMPLE_EMPTY_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertFalse(PostHog::getFeatureFlag('simple-flag', 'distinct-id', True, [], [], []));
        $this->assertFalse(PostHog::getFeatureFlag('simple-flag', 'distinct-id', False, [], [], []));

        $this->assertFalse(PostHog::getFeatureFlag('disabled-flag', 'distinct-id', True, [], [], []));
        $this->assertFalse(PostHog::getFeatureFlag('disabled-flag', 'distinct-id', False, [], [], []));
    }

    public function testFeatureFlagDefaultsComeIntoPlayOnlyWhenDecideErrorsOut()
    {

    }


    public function testFlagExperienceContinuityNotEvaluatedLocally()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::EXPERIENCE_CONITNUITY_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertEquals(PostHog::getFeatureFlag('beta-feature', 'distinct-id', False, [], [], []), 'decide-fallback-value');
    }

    public function testGetAllFlagsWithFallback()
    {

    }

    public function testGetAllFlagsWithFallbackEmptyLocalFlags()
    {

    }

    public function testGetAllFlagsWithNoFallback()
    {

    }

    public function testLoadFeatureFlags()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_GROUP_PROPERTIES_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertEquals(count($this->client->featureFlags), 1);
        $this->assertEquals($this->client->featureFlags[0]["key"], "group-flag");

        $this->assertEquals($this->client->groupTypeMapping, [
            "0" => "company",
            "1" => "project"
        ]);
    }

    public function testLoadFeatureFlagsWrongKey()
    {
        
    }

    public function testSimpleFlag()
    {
        $this->http_client = new MockedHttpClient(host: "app.posthog.com", flagEndpointResponse: MockedResponses::LOCAL_EVALUATION_SIMPLE_REQUEST);
        $this->client = new Client(
            PROJECT_API_KEY,
            [
                "debug" => true,
            ],
            $this->http_client
        );
        PostHog::init(null, null, $this->client);

        $this->assertTrue(PostHog::getFeatureFlag('simple-flag', 'some-distinct-id'));

    }

}