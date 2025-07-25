<?php
/*
* File: ApplicationTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 28.12.22 18:11
* Updated: -
*
* Description:
*  -
*/


namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\CalMag\Application;
use Webklex\CalMag\Config;
use Webklex\CalMag\Translator;

class ApplicationTest extends TestCase {

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
        
        // Reset $_GET to prevent state from persisting between tests
        $_GET = [];
    }

    /**
     * Config test
     *
     * @return void
     */
    public function testApplication(): void {
        $app = new Application();
        self::assertInstanceOf(Application::class, $app);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ACCEPT'] = '';
        $_SERVER['CONTENT_TYPE'] = '';

        ob_start();
        $app->route();
        $output = ob_get_clean();
        self::assertStringContainsString('</html>', $output);
        self::assertStringNotContainsString(Translator::translate("content.calculator.result.title"), $output);
        $_GET = [];

        $_GET["p"] = "eyJmZXJ0aWxpemVyIjoiQXRhbWkgLSBBVEEgQ2FsTWFnIiwiYWRkaXRpdmUiOnsiY2FsY2l1bSI6IkNhQzJIM08ySDJPIiwibWFnbmVzaXVtIjoiTWdPIn0sImFkZGl0aXZlX2NvbmNlbnRyYXRpb24iOnsiY2FsY2l1bSI6MS41LCJtYWduZXNpdW0iOjAuMjR9LCJlbGVtZW50cyI6eyJjYWxjaXVtIjo0NS43LCJtYWduZXNpdW0iOjQuNCwicG90YXNzaXVtIjowfSwiZWxlbWVudF91bml0cyI6eyJjYWxjaXVtIjoibWciLCJtYWduZXNpdW0iOiJtZyIsInBvdGFzc2l1bSI6Im1nIiwiaXJvbiI6Im1nIiwic3VscGhhdGUiOiJtZyIsIm5pdHJhdGUiOiJtZyIsIm5pdHJpdGUiOiJtZyJ9LCJyYXRpbyI6My41LCJyZWdpb24iOiJ1cyIsInZvbHVtZSI6MTB9";
        ob_start();
        $app->route();
        $output = ob_get_clean();
        self::assertStringContainsString(Translator::translate("content.calculator.result.title"), $output);
        self::assertStringContainsString('</html>', $output);
        unset($_GET["p"]);
        $_GET = [];

        $_GET["builder"] = "1";
        $_GET["p"] = "eyJmZXJ0aWxpemVyIjoiQXRhbWkgLSBBVEEgQ2FsTWFnIiwiYWRkaXRpdmUiOnsiY2FsY2l1bSI6IkNhQzJIM08ySDJPIiwibWFnbmVzaXVtIjoiTWdPIn0sImFkZGl0aXZlX2NvbmNlbnRyYXRpb24iOnsiY2FsY2l1bSI6MS41LCJtYWduZXNpdW0iOjAuMjR9LCJlbGVtZW50cyI6eyJjYWxjaXVtIjo0NS43LCJtYWduZXNpdW0iOjQuNCwicG90YXNzaXVtIjowfSwiZWxlbWVudF91bml0cyI6eyJjYWxjaXVtIjoibWciLCJtYWduZXNpdW0iOiJtZyIsInBvdGFzc2l1bSI6Im1nIiwiaXJvbiI6Im1nIiwic3VscGhhdGUiOiJtZyIsIm5pdHJhdGUiOiJtZyIsIm5pdHJpdGUiOiJtZyJ9LCJyYXRpbyI6My41LCJyZWdpb24iOiJ1cyIsInZvbHVtZSI6MTB9";
        ob_start();
        $app->route();
        $output = ob_get_clean();
        self::assertStringContainsString('322,20 g/L', $output);
        self::assertStringContainsString('</html>', $output);
        unset($_GET["builder"], $_GET["p"]);
        $_GET = [];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER["HTTP_ACCEPT"] = "application/json";
        $_SERVER["CONTENT_TYPE"] = "application/json";
        $payload = [
            "fertilizer"             => "",
            "additive"               => [
                "calcium"   => "",
                "magnesium" => ""
            ],
            "additive_concentration" => [
                "calcium"   => 0,
                "magnesium" => 0
            ],
            "region"                 => "de",
            "ratio"                  => 3.5,
            "volume"                 => 5,
            "elements"               => [
                "calcium"   => 0,
                "magnesium" => 0,
                "sulphate"  => 0,
            ],
            "element_units"          => [
                "calcium"   => "mg",
                "magnesium" => "mg"
            ]
        ];
        $stdin = fopen('php://input', 'w');
        $json = json_encode($payload);
        fwrite($stdin, $json);
        ob_start();
        $app->route();
        $output = ob_get_clean();
        $json = json_decode($output, true);
        self::assertIsArray($json);
        self::assertArrayHasKey('version', $json);
        self::assertArrayHasKey('result', $json);
        self::assertArrayHasKey('deficiency', $json["result"]);
        self::assertArrayHasKey('results', $json["result"]);
        self::assertArrayHasKey('table', $json["result"]);

        $_SERVER["HTTP_ACCEPT"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
        $_SERVER["CONTENT_TYPE"] = "text/html; charset=utf-8";
        $_POST = $payload;
        ob_start();
        $app->route();
        $output = ob_get_clean();
        self::assertStringContainsString(Translator::translate("content.calculator.result.title"), $output);
        self::assertStringNotContainsString('371,37 g/L', $output);
        self::assertStringContainsString('</html>', $output);
        
        $_GET["builder"] = "1";
        $_POST = $payload;
        ob_start();
        $app->route();
        $output = ob_get_clean();
        self::assertStringContainsString(Translator::translate("content.calculator.result.title"), $output);
        self::assertStringNotContainsString('371,37 g/L', $output);
        self::assertStringContainsString('</html>', $output);
        unset($_GET["builder"]);
    }
}