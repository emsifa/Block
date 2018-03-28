<?php

use Emsifa\Block;

class BlockTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->block = new Block(__DIR__.'/views');
        $this->block->setDirectory(__DIR__.'/another_views', 'another');
        $this->block->setViewExtension('php');
    }

    public function testSetAndGetDirectory()
    {
        $this->block->setDirectory(__DIR__.'/views');
        $this->assertEquals($this->block->getDirectory(), __DIR__.'/views');

        $namespaces = [
            'foo' => __DIR__.'/somepath/foo',
            'bar' => '/../somepath/bar'
        ];

        foreach($namespaces as $namespace => $dir) {
            $this->block->setDirectory($dir, $namespace);
            $this->assertEquals($this->block->getDirectory($namespace), $dir);
        }
    }

    public function testHas()
    {
        $this->assertTrue($this->block->has('base'), 'view base is exists');
        $this->assertTrue($this->block->has('simple-page'), 'view simple-page is exists');
        $this->assertFalse($this->block->has('widget'), 'view widget doesn\'t exists');
        $this->assertTrue($this->block->has('another::widget'), 'view another::widget is exists');
    }

    public function testSimpleBlocking()
    {
        $this->block->section('a block');
        echo "i am block";
        $this->block->stop();

        $this->block->section('another block');
        echo "i am another block";
        $this->block->stop();

        $this->assertEquals(trim($this->block->get('a block')), 'i am block');
        $this->assertEquals(trim($this->block->get('another block')), 'i am another block');
    }

    public function testParentBlocking()
    {
        $this->block->section('js');
            echo $this->block->parent();
            echo "<script src='b.js'></script>";
        $this->block->stop();

        $this->block->section('js');
        echo "<script src='a.js'></script>";
        $this->block->stop();

        $this->assertEquals(trim($this->block->get('js')), "<script src='a.js'></script><script src='b.js'></script>");
    }

    public function testSimpleRender()
    {
        $output = $this->block->render('simple-page', [
            'message' => 'Simple Page'
        ]);

        $this->assertEquals($output, '<h1>Simple Page</h1>');
    }


    public function testShare()
    {
        $this->block->share('message', 'test share variable');
        $output = $this->block->render('sharing');

        $this->assertOutputSimilar($output, '
            <div>
                <h1>test share variable</h1>
                <p>test share variable</p>
            </div>
        ');
    }

    public function testEscaping()
    {
        $output = $this->block->render('escaping', [
            'html' => '<h1>Foo</h1>',
            'script' => '<script>Bar</script>'
        ]);

        $this->assertOutputSimilar($output, '
            <div>
                &lt;h1&gt;Foo&lt;/h1&gt;
                &lt;script&gt;Bar&lt;/script&gt;
            </div>
        ');
    }

    public function testGetter()
    {
        $output = $this->block->render('getter', [
            'user' => [
                'name' => 'John Doe',
                'city' => [
                    'name' => 'Jakarta',
                ]
            ]
        ]);

        $this->assertOutputSimilar($output, '
            <div>
                Name: John Doe
                <br/>
                City: Jakarta
                <br/>
                Province: Unknown
            </div>
        ');
    }

    public function testDotPathSeparator()
    {
        $output = $this->block->render('foo.bar.baz', [
            'message' => 'Simple Page'
        ]);

        $this->assertEquals($output, '<h1>Simple Page</h1>');
    }

    public function testCustomExtension()
    {
        $this->block->setViewExtension('block');
        $output = $this->block->render('simple-page', [
            'message' => 'Simple Page'
        ]);

        $this->assertEquals($output, '<h1>Simple Page</h1>');
    }

    public function testRenderWithInsert()
    {
        $output = $this->block->render('page-with-insert', [
            'title' => 'Page Title'
        ]);

        $this->assertOutputSimilar($output, '
            <h1>Page Title</h1>
            <div>widget content</div>
        ');
    }

    public function testRenderWithPut()
    {
        $output = $this->block->render('page-with-put', [
            'title' => 'Page Title'
        ]);

        $this->assertOutputSimilar($output, '
            <h1>Page Title</h1>
            <div>widget content</div>
        ');
    }

    public function testViewComposer()
    {
        $this->block->composer('composer', function($data) {
            return [
                'data_from_composer' => 'bar'
            ];
        });

        $output = $this->block->render('composer', [
            'data_from_render' => 'foo'
        ]);

        $this->assertOutputSimilar($output, '
            <h1>foo bar</h1>
        ');
    }

    public function testExtend()
    {
        $output = $this->block->render('page-complex', [
            'title' => 'Page Complex'
        ]);

        $this->assertOutputSimilar($output, '
            <html>
                <head>
                    <title>Page Complex</title>
                    <link href="a.css"/>
                    <link href="b.css"/>
                </head>
                <body class="page-test">
                    <h1>Component</h1>
                    <h1>Page Complex</h1>
                    <div id="container">
                        <div id="sidebar">
                            <h1>Widget with js</h1>
                        </div>
                        <div id="content">
                            page content
                        </div>
                    </div>
                    <script src="a.js"></script>
                    <script>component.init()</script>
                    <script src="b.js"></script>
                    <script>js.here()</script>
                </body>
            </html>
        ');
    }

    public function testAppend()
    {
        $output = $this->block->render('page-append-js', [
            'title' => 'Test Append'
        ]);

        $this->assertOutputSimilar($output, '
            <html>
                <head>
                    <title>Test Append</title>
                    <link href="a.css"/>
                </head>
                <body class="page-test">
                    <script src="a.js"></script>
                    <script src="b.js"></script>
                </body>
            </html>
        ');
    }

    public function testPrepend()
    {
        $output = $this->block->render('page-prepend-js', [
            'title' => 'Test Prepend'
        ]);

        $this->assertOutputSimilar($output, '
            <html>
                <head>
                    <title>Test Prepend</title>
                    <link href="a.css"/>
                </head>
                <body class="page-test">
                    <script src="b.js"></script>
                    <script src="a.js"></script>
                </body>
            </html>
        ');
    }

    public function testComponent()
    {
        $output = $this->block->render('page-with-component');

        $this->assertOutputSimilar($output, '
            <div>
                <div class="alert alert-info">
                    <h4>
                        <span>Alert Title</span>
                    </h4>
                    <p>Lorem ipsum dolor sit amet</p>
                    <div class="whatever">
                        <div class="var">
                            <strong>Hola</strong>
                        </div>
                        <div class="slot">
                            <div>
                                Foobar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ');
    }

    public function testShareRenderDataAcrossSubViews()
    {
        $output = $this->block->render('page-share-data-across-subviews', [
            'message' => "foobar"
        ]);

        $this->assertOutputSimilar($output, '
            <div>
                <template>foobar</template>
                <h1>foobar</h1>
                <div class="message-component">foobar</div>
                <div class="another-message-component">
                    <h3>foobar</h3>
                    <p>Slot</p>
                </div>
            </div>
        ');
    }

    protected function resolveWhitespaces($str)
    {
        return trim(preg_replace("/\s+/", " ", $str));
    }

    protected function assertOutputSimilar($output, $like, $message = null)
    {
        return $this->assertEquals(
            $this->resolveWhitespaces($like),
            $this->resolveWhitespaces($output),
            $message
        );
    }

}
