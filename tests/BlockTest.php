<?php

use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{

    protected function setUp()
    {
        if (!class_exists('Block')) {
            class_alias('Emsifa\Block', 'Block');
        }
        Block::setDirectory(__DIR__.'/views');
        Block::setDirectory(__DIR__.'/another_views', 'another');
    }
    
    public function testSetAndGetDirectory()
    {
        Block::setDirectory(__DIR__.'/views');
        $this->assertEquals(Block::getDirectory(), __DIR__.'/views');
        
        $namespaces = [
            'foo' => __DIR__.'/somepath/foo',
            'bar' => '/../somepath/bar'
        ];
        
        foreach($namespaces as $namespace => $dir) {
            Block::setDirectory($dir, $namespace);
            $this->assertEquals(Block::getDirectory($namespace), $dir);
        }
    }
    
    public function testHas()
    {
        $this->assertTrue(Block::has('base'), 'view base is exists');
        $this->assertTrue(Block::has('simple-page'), 'view simple-page is exists');
        $this->assertFalse(Block::has('widget'), 'view widget doesn\'t exists');
        $this->assertTrue(Block::has('another::widget'), 'view another::widget is exists');
    }

    public function testSimpleBlocking()
    {
        Block::start('a block');
        echo "i am block";
        Block::stop();

        Block::start('another block');
        echo "i am another block";
        Block::stop();

        $this->assertEquals(trim(Block::get('a block')), 'i am block');
        $this->assertEquals(trim(Block::get('another block')), 'i am another block');
    }

    public function testParentBlocking()
    {
        Block::start('js');
            echo Block::parent();
            echo "<script src='b.js'></script>";
        Block::stop();

        Block::start('js');
        echo "<script src='a.js'></script>";
        Block::stop();

        $this->assertEquals(trim(Block::get('js')), "<script src='a.js'></script><script src='b.js'></script>");
    }

    public function testSimpleRender()
    {
        $output = Block::render('simple-page', [
            'message' => 'Simple Page'
        ]);

        $this->assertEquals($output, '<h1>Simple Page</h1>');
    }

    public function testRenderWithInsert()
    {
        $output = Block::render('page-with-insert', [
            'title' => 'Page Title'
        ]);

        $this->assertOutputSimilar($output, '
            <h1>Page Title</h1> 
            <div>widget content</div>
        ');
    }

    public function testExtend()
    {
        $output = Block::render('page-complex', [
            'title' => 'Page Complex'
        ]);

        $this->assertOutputSimilar($output, '
            <html>
                <head>
                    <title>Page Complex</title>
                    <link href="a.css"/>
                    <link href="b.css"/>
                </head>
                <body>
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
                    <script src="b.js"></script>
                    <script>js.here()</script>
                </body>
            </html>
        ');
    }

    protected function resolveWhitespaces($str)
    {
        return trim(preg_replace("/\s+/", " ", $str));
    }

    protected function assertOutputSimilar($output, $like, $message = null)
    {
        return $this->assertEquals(
            $this->resolveWhitespaces($output),
            $this->resolveWhitespaces($like),
            $message
        );
    }

}
