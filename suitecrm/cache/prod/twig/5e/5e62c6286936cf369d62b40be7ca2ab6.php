<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @ApiPlatform/SwaggerUi/index.html.twig */
class __TwigTemplate_713fe01f18fff325c6fe5620f601d567 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'head_metas' => [$this, 'block_head_metas'],
            'title' => [$this, 'block_title'],
            'stylesheet' => [$this, 'block_stylesheet'],
            'head_javascript' => [$this, 'block_head_javascript'],
            'header' => [$this, 'block_header'],
            'javascript' => [$this, 'block_javascript'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html>
<head>
    ";
        // line 4
        yield from $this->unwrap()->yieldBlock('head_metas', $context, $blocks);
        // line 7
        yield "
    ";
        // line 8
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        // line 11
        yield "
    ";
        // line 12
        yield from $this->unwrap()->yieldBlock('stylesheet', $context, $blocks);
        // line 18
        yield "
    ";
        // line 19
        $context["oauth_data"] = ["oauth" => Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, ($context["swagger_data"] ?? null), "oauth", [], "any", false, false, false, 19), ["redirectUrl" => $this->extensions['Symfony\Bridge\Twig\Extension\HttpFoundationExtension']->generateAbsoluteUrl($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/swagger-ui/oauth2-redirect.html", ($context["assetPackage"] ?? null)))])];
        // line 20
        yield "
    ";
        // line 21
        yield from $this->unwrap()->yieldBlock('head_javascript', $context, $blocks);
        // line 25
        yield "</head>

<body>
<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"svg-icons\">
    <defs>
        <symbol viewBox=\"0 0 20 20\" id=\"unlocked\">
            <path d=\"M15.8 8H14V5.6C14 2.703 12.665 1 10 1 7.334 1 6 2.703 6 5.6V6h2v-.801C8 3.754 8.797 3 10 3c1.203 0 2 .754 2 2.199V8H4c-.553 0-1 .646-1 1.199V17c0 .549.428 1.139.951 1.307l1.197.387C5.672 18.861 6.55 19 7.1 19h5.8c.549 0 1.428-.139 1.951-.307l1.196-.387c.524-.167.953-.757.953-1.306V9.199C17 8.646 16.352 8 15.8 8z\"></path>
        </symbol>

        <symbol viewBox=\"0 0 20 20\" id=\"locked\">
            <path d=\"M15.8 8H14V5.6C14 2.703 12.665 1 10 1 7.334 1 6 2.703 6 5.6V8H4c-.553 0-1 .646-1 1.199V17c0 .549.428 1.139.951 1.307l1.197.387C5.672 18.861 6.55 19 7.1 19h5.8c.549 0 1.428-.139 1.951-.307l1.196-.387c.524-.167.953-.757.953-1.306V9.199C17 8.646 16.352 8 15.8 8zM12 8H8V5.199C8 3.754 8.797 3 10 3c1.203 0 2 .754 2 2.199V8z\"></path>
        </symbol>

        <symbol viewBox=\"0 0 20 20\" id=\"close\">
            <path d=\"M14.348 14.849c-.469.469-1.229.469-1.697 0L10 11.819l-2.651 3.029c-.469.469-1.229.469-1.697 0-.469-.469-.469-1.229 0-1.697l2.758-3.15-2.759-3.152c-.469-.469-.469-1.228 0-1.697.469-.469 1.228-.469 1.697 0L10 8.183l2.651-3.031c.469-.469 1.228-.469 1.697 0 .469.469.469 1.229 0 1.697l-2.758 3.152 2.758 3.15c.469.469.469 1.229 0 1.698z\"></path>
        </symbol>

        <symbol viewBox=\"0 0 20 20\" id=\"large-arrow\">
            <path d=\"M13.25 10L6.109 2.58c-.268-.27-.268-.707 0-.979.268-.27.701-.27.969 0l7.83 7.908c.268.271.268.709 0 .979l-7.83 7.908c-.268.271-.701.27-.969 0-.268-.269-.268-.707 0-.979L13.25 10z\"></path>
        </symbol>

        <symbol viewBox=\"0 0 20 20\" id=\"large-arrow-down\">
            <path d=\"M17.418 6.109c.272-.268.709-.268.979 0s.271.701 0 .969l-7.908 7.83c-.27.268-.707.268-.979 0l-7.908-7.83c-.27-.268-.27-.701 0-.969.271-.268.709-.268.979 0L10 13.25l7.418-7.141z\"></path>
        </symbol>


        <symbol viewBox=\"0 0 24 24\" id=\"jump-to\">
            <path d=\"M19 7v4H5.83l3.58-3.59L8 6l-6 6 6 6 1.41-1.41L5.83 13H21V7z\"></path>
        </symbol>

        <symbol viewBox=\"0 0 24 24\" id=\"expand\">
            <path d=\"M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z\"></path>
        </symbol>

    </defs>
</svg>

";
        // line 62
        yield from $this->unwrap()->yieldBlock('header', $context, $blocks);
        // line 67
        yield "
";
        // line 68
        if (($context["showWebby"] ?? null)) {
            // line 69
            yield "    <div class=\"web\"><img src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/web.png", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></div>
    <div class=\"webby\"><img src=\"";
            // line 70
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/webby.png", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></div>
";
        }
        // line 72
        yield "
<div id=\"swagger-ui\" class=\"api-platform\"></div>

<div class=\"swagger-ui\" id=\"formats\">
    <div class=\"information-container wrapper\">
        <div class=\"info\">
            Available formats:
            ";
        // line 79
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::keys(($context["formats"] ?? null)));
        foreach ($context['_seq'] as $context["_key"] => $context["format"]) {
            // line 80
            yield "                <a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath(($context["originalRoute"] ?? null), Twig\Extension\CoreExtension::merge(($context["originalRouteParams"] ?? null), ["_format" => $context["format"]])), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["format"], "html", null, true);
            yield "</a>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['format'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 82
        yield "            <br>
            Other API docs:
            ";
        // line 84
        $context["active_ui"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 84), "get", ["ui", "swagger_ui"], "method", false, false, false, 84);
        // line 85
        yield "            ";
        if ((($context["swaggerUiEnabled"] ?? null) && (($context["active_ui"] ?? null) != "swagger_ui"))) {
            yield "<a href=\"";
            yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("api_doc");
            yield "\">Swagger UI</a>";
        }
        // line 86
        yield "            ";
        if ((($context["reDocEnabled"] ?? null) && (($context["active_ui"] ?? null) != "re_doc"))) {
            yield "<a href=\"";
            yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("api_doc", ["ui" => "re_doc"]);
            yield "\">ReDoc</a>";
        }
        // line 87
        yield "            ";
        if (( !($context["graphQlEnabled"] ?? null) || ($context["graphiQlEnabled"] ?? null))) {
            yield "<a ";
            if (($context["graphiQlEnabled"] ?? null)) {
                yield "href=\"";
                yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("api_graphql_graphiql");
                yield "\"";
            }
            yield " class=\"graphiql-link\">GraphiQL</a>";
        }
        // line 88
        yield "            ";
        if (($context["graphQlPlaygroundEnabled"] ?? null)) {
            yield "<a href=\"";
            yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("api_graphql_graphql_playground");
            yield "\">GraphQL Playground (deprecated)</a>";
        }
        // line 89
        yield "        </div>
    </div>
</div>

";
        // line 93
        yield from $this->unwrap()->yieldBlock('javascript', $context, $blocks);
        // line 104
        yield "
</body>
</html>
";
        return; yield '';
    }

    // line 4
    public function block_head_metas($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "        <meta charset=\"UTF-8\">
    ";
        return; yield '';
    }

    // line 8
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 9
        yield "        <title>";
        if (($context["title"] ?? null)) {
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null), "html", null, true);
            yield " - ";
        }
        yield "API Platform</title>
    ";
        return; yield '';
    }

    // line 12
    public function block_stylesheet($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 13
        yield "        <link rel=\"stylesheet\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/fonts/open-sans/400.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
        <link rel=\"stylesheet\" href=\"";
        // line 14
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/fonts/open-sans/700.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
        <link rel=\"stylesheet\" href=\"";
        // line 15
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/swagger-ui/swagger-ui.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
        <link rel=\"stylesheet\" href=\"";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/style.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
    ";
        return; yield '';
    }

    // line 21
    public function block_head_javascript($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 22
        yield "        ";
        // line 23
        yield "        <script id=\"swagger-data\" type=\"application/json\">";
        yield json_encode(Twig\Extension\CoreExtension::merge(($context["swagger_data"] ?? null), ($context["oauth_data"] ?? null)), 65);
        yield "</script>
    ";
        return; yield '';
    }

    // line 62
    public function block_header($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 63
        yield "    <header>
        <a id=\"logo\" href=\"https://api-platform.com\"><img src=\"";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/logo-header.svg", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\" alt=\"API Platform\"></a>
    </header>
";
        return; yield '';
    }

    // line 93
    public function block_javascript($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 94
        yield "    ";
        if (((($context["reDocEnabled"] ?? null) &&  !($context["swaggerUiEnabled"] ?? null)) || (($context["reDocEnabled"] ?? null) && ("re_doc" == ($context["active_ui"] ?? null))))) {
            // line 95
            yield "        <script src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/redoc/redoc.standalone.js", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></script>
        <script src=\"";
            // line 96
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/init-redoc-ui.js", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></script>
    ";
        } else {
            // line 98
            yield "        <script src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/swagger-ui/swagger-ui-bundle.js", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></script>
        <script src=\"";
            // line 99
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/swagger-ui/swagger-ui-standalone-preset.js", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></script>
        <script src=\"";
            // line 100
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/init-swagger-ui.js", ($context["assetPackage"] ?? null)), "html", null, true);
            yield "\"></script>
    ";
        }
        // line 102
        yield "    <script src=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/init-common-ui.js", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\" defer></script>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "@ApiPlatform/SwaggerUi/index.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  313 => 102,  308 => 100,  304 => 99,  299 => 98,  294 => 96,  289 => 95,  286 => 94,  282 => 93,  274 => 64,  271 => 63,  267 => 62,  259 => 23,  257 => 22,  253 => 21,  246 => 16,  242 => 15,  238 => 14,  233 => 13,  229 => 12,  218 => 9,  214 => 8,  205 => 4,  197 => 104,  195 => 93,  189 => 89,  182 => 88,  171 => 87,  164 => 86,  157 => 85,  155 => 84,  151 => 82,  140 => 80,  136 => 79,  127 => 72,  122 => 70,  117 => 69,  115 => 68,  112 => 67,  110 => 62,  71 => 25,  69 => 21,  66 => 20,  64 => 19,  61 => 18,  59 => 12,  56 => 11,  54 => 8,  51 => 7,  49 => 4,  44 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "@ApiPlatform/SwaggerUi/index.html.twig", "/var/www/html/vendor/api-platform/core/src/Symfony/Bundle/Resources/views/SwaggerUi/index.html.twig");
    }
}
