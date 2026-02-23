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

/* @ApiPlatform/Graphiql/index.html.twig */
class __TwigTemplate_d771ab5aa37f7f7093e5783293521de6 extends Template
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
            'head_stylesheets' => [$this, 'block_head_stylesheets'],
            'head_javascript' => [$this, 'block_head_javascript'],
            'body_javascript' => [$this, 'block_body_javascript'],
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
        yield from $this->unwrap()->yieldBlock('head_stylesheets', $context, $blocks);
        // line 16
        yield "
    ";
        // line 17
        yield from $this->unwrap()->yieldBlock('head_javascript', $context, $blocks);
        // line 21
        yield "</head>

<body>
<div id=\"graphiql\">Loading...</div>

";
        // line 26
        yield from $this->unwrap()->yieldBlock('body_javascript', $context, $blocks);
        // line 32
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
    public function block_head_stylesheets($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 13
        yield "        <link rel=\"stylesheet\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/graphiql/graphiql.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
        <link rel=\"stylesheet\" href=\"";
        // line 14
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/graphiql-style.css", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\">
    ";
        return; yield '';
    }

    // line 17
    public function block_head_javascript($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 18
        yield "        ";
        // line 19
        yield "        <script id=\"graphiql-data\" type=\"application/json\">";
        yield json_encode(($context["graphiql_data"] ?? null), 65);
        yield "</script>
    ";
        return; yield '';
    }

    // line 26
    public function block_body_javascript($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 27
        yield "    <script src=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/react/react.production.min.js", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\"></script>
    <script src=\"";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/react/react-dom.production.min.js", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\"></script>
    <script src=\"";
        // line 29
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/graphiql/graphiql.min.js", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\"></script>
    <script src=\"";
        // line 30
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("bundles/apiplatform/init-graphiql.js", ($context["assetPackage"] ?? null)), "html", null, true);
        yield "\"></script>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "@ApiPlatform/Graphiql/index.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  153 => 30,  149 => 29,  145 => 28,  140 => 27,  136 => 26,  128 => 19,  126 => 18,  122 => 17,  115 => 14,  110 => 13,  106 => 12,  95 => 9,  91 => 8,  82 => 4,  74 => 32,  72 => 26,  65 => 21,  63 => 17,  60 => 16,  58 => 12,  55 => 11,  53 => 8,  50 => 7,  48 => 4,  43 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "@ApiPlatform/Graphiql/index.html.twig", "/var/www/html/vendor/api-platform/core/src/Symfony/Bundle/Resources/views/Graphiql/index.html.twig");
    }
}
