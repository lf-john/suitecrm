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

/* @VichUploader/Form/fields.html.twig */
class __TwigTemplate_4bd4e1c8748f87801ab57b8aaef31ca8 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'vich_file_row' => [$this, 'block_vich_file_row'],
            'vich_file_widget' => [$this, 'block_vich_file_widget'],
            'vich_image_row' => [$this, 'block_vich_image_row'],
            'vich_image_widget' => [$this, 'block_vich_image_widget'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield from $this->unwrap()->yieldBlock('vich_file_row', $context, $blocks);
        // line 6
        yield from $this->unwrap()->yieldBlock('vich_file_widget', $context, $blocks);
        // line 24
        yield from $this->unwrap()->yieldBlock('vich_image_row', $context, $blocks);
        // line 29
        yield from $this->unwrap()->yieldBlock('vich_image_widget', $context, $blocks);
        return; yield '';
    }

    // line 1
    public function block_vich_file_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 2
        $context["force_error"] = true;
        // line 3
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 6
    public function block_vich_file_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 7
        yield "<div class=\"vich-file\">";
        // line 8
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["errors"] ?? null)) > 0)) {
            // line 9
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 9)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 9), "")) : ("")) . " is-invalid"))]);
        }
        // line 11
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "file", [], "any", false, false, false, 11), 'widget', ["attr" => ($context["attr"] ?? null)]);
        // line 12
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "delete", [], "any", true, true, false, 12)) {
            // line 13
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "delete", [], "any", false, false, false, 13), 'row');
        }
        // line 16
        if (($context["download_uri"] ?? null)) {
            // line 17
            yield "<a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["asset_helper"] ?? null) === true)) ? ($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl(($context["download_uri"] ?? null))) : (($context["download_uri"] ?? null))), "html", null, true);
            yield "\" download>
                ";
            // line 18
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["translation_domain"] ?? null) === false)) ? (($context["download_label"] ?? null)) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["download_label"] ?? null), [], ($context["translation_domain"] ?? null)))), "html", null, true);
            yield "
            </a>";
        }
        // line 21
        yield "</div>";
        return; yield '';
    }

    // line 24
    public function block_vich_image_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 25
        $context["force_error"] = true;
        // line 26
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 29
    public function block_vich_image_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 30
        yield "<div class=\"vich-image\">";
        // line 31
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["errors"] ?? null)) > 0)) {
            // line 32
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 32)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 32), "")) : ("")) . " is-invalid"))]);
        }
        // line 34
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "file", [], "any", false, false, false, 34), 'widget', ["attr" => ($context["attr"] ?? null)]);
        // line 35
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "delete", [], "any", true, true, false, 35)) {
            // line 36
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "delete", [], "any", false, false, false, 36), 'row');
        }
        // line 39
        if (($context["image_uri"] ?? null)) {
            // line 40
            yield "<a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["asset_helper"] ?? null) === true)) ? ($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl(($context["image_uri"] ?? null))) : (($context["image_uri"] ?? null))), "html", null, true);
            yield "\" download>
                <img src=\"";
            // line 41
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["asset_helper"] ?? null) === true)) ? ($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl(($context["image_uri"] ?? null))) : (($context["image_uri"] ?? null))), "html", null, true);
            yield "\" alt=\"\" />
            </a>";
        }
        // line 44
        if (($context["download_uri"] ?? null)) {
            // line 45
            yield "<a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["asset_helper"] ?? null) === true)) ? ($this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl(($context["download_uri"] ?? null))) : (($context["download_uri"] ?? null))), "html", null, true);
            yield "\" download>
                ";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["translation_domain"] ?? null) === false)) ? (($context["download_label"] ?? null)) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["download_label"] ?? null), [], ($context["translation_domain"] ?? null)))), "html", null, true);
            yield "
            </a>";
        }
        // line 49
        yield "</div>";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "@VichUploader/Form/fields.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  152 => 49,  147 => 46,  142 => 45,  140 => 44,  135 => 41,  130 => 40,  128 => 39,  125 => 36,  123 => 35,  121 => 34,  118 => 32,  116 => 31,  114 => 30,  110 => 29,  105 => 26,  103 => 25,  99 => 24,  94 => 21,  89 => 18,  84 => 17,  82 => 16,  79 => 13,  77 => 12,  75 => 11,  72 => 9,  70 => 8,  68 => 7,  64 => 6,  59 => 3,  57 => 2,  53 => 1,  48 => 29,  46 => 24,  44 => 6,  42 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "@VichUploader/Form/fields.html.twig", "/var/www/html/vendor/vich/uploader-bundle/templates/Form/fields.html.twig");
    }
}
