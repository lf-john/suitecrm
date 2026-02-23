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

/* bootstrap_3_layout.html.twig */
class __TwigTemplate_9811711c90e8b6b65ae89e89b47a236a extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        // line 1
        $_trait_0 = $this->loadTemplate("bootstrap_base_layout.html.twig", "bootstrap_3_layout.html.twig", 1);
        if (!$_trait_0->unwrap()->isTraitable()) {
            throw new RuntimeError('Template "'."bootstrap_base_layout.html.twig".'" cannot be used as a trait.', 1, $this->source);
        }
        $_trait_0_blocks = $_trait_0->unwrap()->getBlocks();

        $this->traits = $_trait_0_blocks;

        $this->blocks = array_merge(
            $this->traits,
            [
                'form_widget_simple' => [$this, 'block_form_widget_simple'],
                'button_widget' => [$this, 'block_button_widget'],
                'money_widget' => [$this, 'block_money_widget'],
                'checkbox_widget' => [$this, 'block_checkbox_widget'],
                'radio_widget' => [$this, 'block_radio_widget'],
                'choice_widget_collapsed' => [$this, 'block_choice_widget_collapsed'],
                'form_label' => [$this, 'block_form_label'],
                'choice_label' => [$this, 'block_choice_label'],
                'checkbox_label' => [$this, 'block_checkbox_label'],
                'radio_label' => [$this, 'block_radio_label'],
                'checkbox_radio_label' => [$this, 'block_checkbox_radio_label'],
                'form_row' => [$this, 'block_form_row'],
                'button_row' => [$this, 'block_button_row'],
                'choice_row' => [$this, 'block_choice_row'],
                'date_row' => [$this, 'block_date_row'],
                'time_row' => [$this, 'block_time_row'],
                'datetime_row' => [$this, 'block_datetime_row'],
                'checkbox_row' => [$this, 'block_checkbox_row'],
                'radio_row' => [$this, 'block_radio_row'],
                'form_errors' => [$this, 'block_form_errors'],
                'form_help' => [$this, 'block_form_help'],
            ]
        );
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 2
        yield "
";
        // line 4
        yield "
";
        // line 5
        yield from $this->unwrap()->yieldBlock('form_widget_simple', $context, $blocks);
        // line 11
        yield "
";
        // line 12
        yield from $this->unwrap()->yieldBlock('button_widget', $context, $blocks);
        // line 16
        yield "
";
        // line 17
        yield from $this->unwrap()->yieldBlock('money_widget', $context, $blocks);
        // line 34
        yield "
";
        // line 35
        yield from $this->unwrap()->yieldBlock('checkbox_widget', $context, $blocks);
        // line 45
        yield "
";
        // line 46
        yield from $this->unwrap()->yieldBlock('radio_widget', $context, $blocks);
        // line 56
        yield "
";
        // line 57
        yield from $this->unwrap()->yieldBlock('choice_widget_collapsed', $context, $blocks);
        // line 61
        yield "
";
        // line 63
        yield "
";
        // line 64
        yield from $this->unwrap()->yieldBlock('form_label', $context, $blocks);
        // line 68
        yield "
";
        // line 69
        yield from $this->unwrap()->yieldBlock('choice_label', $context, $blocks);
        // line 74
        yield "
";
        // line 75
        yield from $this->unwrap()->yieldBlock('checkbox_label', $context, $blocks);
        // line 80
        yield "
";
        // line 81
        yield from $this->unwrap()->yieldBlock('radio_label', $context, $blocks);
        // line 86
        yield "
";
        // line 87
        yield from $this->unwrap()->yieldBlock('checkbox_radio_label', $context, $blocks);
        // line 127
        yield "
";
        // line 129
        yield "
";
        // line 130
        yield from $this->unwrap()->yieldBlock('form_row', $context, $blocks);
        // line 142
        yield "
";
        // line 143
        yield from $this->unwrap()->yieldBlock('button_row', $context, $blocks);
        // line 148
        yield "
";
        // line 149
        yield from $this->unwrap()->yieldBlock('choice_row', $context, $blocks);
        // line 153
        yield "
";
        // line 154
        yield from $this->unwrap()->yieldBlock('date_row', $context, $blocks);
        // line 158
        yield "
";
        // line 159
        yield from $this->unwrap()->yieldBlock('time_row', $context, $blocks);
        // line 163
        yield "
";
        // line 164
        yield from $this->unwrap()->yieldBlock('datetime_row', $context, $blocks);
        // line 168
        yield "
";
        // line 169
        yield from $this->unwrap()->yieldBlock('checkbox_row', $context, $blocks);
        // line 176
        yield "
";
        // line 177
        yield from $this->unwrap()->yieldBlock('radio_row', $context, $blocks);
        // line 184
        yield "
";
        // line 186
        yield "
";
        // line 187
        yield from $this->unwrap()->yieldBlock('form_errors', $context, $blocks);
        // line 198
        yield "
";
        // line 200
        yield "
";
        // line 201
        yield from $this->unwrap()->yieldBlock('form_help', $context, $blocks);
        return; yield '';
    }

    // line 5
    public function block_form_widget_simple($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        if (( !array_key_exists("type", $context) || !CoreExtension::inFilter(($context["type"] ?? null), ["file", "hidden"]))) {
            // line 7
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 7)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 7), "")) : ("")) . " form-control"))]);
        }
        // line 9
        yield from $this->yieldParentBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 12
    public function block_button_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 13
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 13)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 13), "btn-default")) : ("btn-default")) . " btn"))]);
        // line 14
        yield from $this->yieldParentBlock("button_widget", $context, $blocks);
        return; yield '';
    }

    // line 17
    public function block_money_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 18
        $context["prepend"] =  !(is_string($__internal_compile_0 = ($context["money_pattern"] ?? null)) && is_string($__internal_compile_1 = "{{") && str_starts_with($__internal_compile_0, $__internal_compile_1));
        // line 19
        yield "    ";
        $context["append"] =  !(is_string($__internal_compile_2 = ($context["money_pattern"] ?? null)) && is_string($__internal_compile_3 = "}}") && str_ends_with($__internal_compile_2, $__internal_compile_3));
        // line 20
        yield "    ";
        if ((($context["prepend"] ?? null) || ($context["append"] ?? null))) {
            // line 21
            yield "        <div class=\"input-group\">
            ";
            // line 22
            if (($context["prepend"] ?? null)) {
                // line 23
                yield "                <span class=\"input-group-addon\">";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->encodeCurrency($this->env, ($context["money_pattern"] ?? null));
                yield "</span>
            ";
            }
            // line 25
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
            // line 26
            if (($context["append"] ?? null)) {
                // line 27
                yield "                <span class=\"input-group-addon\">";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->encodeCurrency($this->env, ($context["money_pattern"] ?? null));
                yield "</span>
            ";
            }
            // line 29
            yield "        </div>
    ";
        } else {
            // line 31
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        }
        return; yield '';
    }

    // line 35
    public function block_checkbox_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 36
        $context["parent_label_class"] = ((array_key_exists("parent_label_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["parent_label_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 36)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 36), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 36)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 36), "")) : (""))));
        // line 37
        if (CoreExtension::inFilter("checkbox-inline", ($context["parent_label_class"] ?? null))) {
            // line 38
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("checkbox_widget", $context, $blocks)]);
        } else {
            // line 40
            yield "<div class=\"checkbox\">";
            // line 41
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("checkbox_widget", $context, $blocks)]);
            // line 42
            yield "</div>";
        }
        return; yield '';
    }

    // line 46
    public function block_radio_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 47
        $context["parent_label_class"] = ((array_key_exists("parent_label_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["parent_label_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 47)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 47), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 47)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 47), "")) : (""))));
        // line 48
        if (CoreExtension::inFilter("radio-inline", ($context["parent_label_class"] ?? null))) {
            // line 49
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("radio_widget", $context, $blocks)]);
        } else {
            // line 51
            yield "<div class=\"radio\">";
            // line 52
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("radio_widget", $context, $blocks)]);
            // line 53
            yield "</div>";
        }
        return; yield '';
    }

    // line 57
    public function block_choice_widget_collapsed($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 58
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 58)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 58), "")) : ("")) . " form-control"))]);
        // line 59
        yield from $this->yieldParentBlock("choice_widget_collapsed", $context, $blocks);
        return; yield '';
    }

    // line 64
    public function block_form_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 65
        $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 65)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 65), "")) : ("")) . " control-label"))]);
        // line 66
        yield from $this->yieldParentBlock("form_label", $context, $blocks);
        return; yield '';
    }

    // line 69
    public function block_choice_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 71
        $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::replace(((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 71)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 71), "")) : ("")), ["checkbox-inline" => "", "radio-inline" => ""]))]);
        // line 72
        yield from         $this->unwrap()->yieldBlock("form_label", $context, $blocks);
        return; yield '';
    }

    // line 75
    public function block_checkbox_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 76
        $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["for" => ($context["id"] ?? null)]);
        // line 78
        yield from         $this->unwrap()->yieldBlock("checkbox_radio_label", $context, $blocks);
        return; yield '';
    }

    // line 81
    public function block_radio_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 82
        $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["for" => ($context["id"] ?? null)]);
        // line 84
        yield from         $this->unwrap()->yieldBlock("checkbox_radio_label", $context, $blocks);
        return; yield '';
    }

    // line 87
    public function block_checkbox_radio_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 89
        if (array_key_exists("widget", $context)) {
            // line 90
            if (($context["required"] ?? null)) {
                // line 91
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 91)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 91), "")) : ("")) . " required"))]);
            }
            // line 93
            if (array_key_exists("parent_label_class", $context)) {
                // line 94
                $context["embed_label_classes"] = Twig\Extension\CoreExtension::filter($this->env, Twig\Extension\CoreExtension::split($this->env->getCharset(), ($context["parent_label_class"] ?? null), " "), function ($__class__) use ($context, $macros) { $context["class"] = $__class__; return CoreExtension::inFilter(($context["class"] ?? null), ["checkbox-inline", "radio-inline"]); });
                // line 95
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 95)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 95), "")) : ("")) . " ") . Twig\Extension\CoreExtension::join(($context["embed_label_classes"] ?? null), " ")))]);
            }
            // line 97
            if (( !(($context["label"] ?? null) === false) && Twig\Extension\CoreExtension::testEmpty(($context["label"] ?? null)))) {
                // line 98
                if ( !Twig\Extension\CoreExtension::testEmpty(($context["label_format"] ?? null))) {
                    // line 99
                    $context["label"] = Twig\Extension\CoreExtension::replace(($context["label_format"] ?? null), ["%name%" =>                     // line 100
($context["name"] ?? null), "%id%" =>                     // line 101
($context["id"] ?? null)]);
                } else {
                    // line 104
                    $context["label"] = $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->humanize(($context["name"] ?? null));
                }
            }
            // line 107
            yield "<label";
            $__internal_compile_4 = $context;
            $__internal_compile_5 = ["attr" => ($context["label_attr"] ?? null)];
            if (!is_iterable($__internal_compile_5)) {
                throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 107, $this->getSourceContext());
            }
            $__internal_compile_5 = CoreExtension::toArray($__internal_compile_5);
            $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_5));
            yield from             $this->unwrap()->yieldBlock("attributes", $context, $blocks);
            $context = $__internal_compile_4;
            yield ">";
            // line 109
            yield ($context["widget"] ?? null);
            yield " ";
            if ( !(($context["label"] ?? null) === false)) {
                // line 110
                if ((($context["translation_domain"] ?? null) === false)) {
                    // line 111
                    if ((($context["label_html"] ?? null) === false)) {
                        // line 112
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
                    } else {
                        // line 114
                        yield ($context["label"] ?? null);
                    }
                } else {
                    // line 117
                    if ((($context["label_html"] ?? null) === false)) {
                        // line 118
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)), "html", null, true);
                    } else {
                        // line 120
                        yield $this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null));
                    }
                }
            }
            // line 124
            yield "</label>";
        }
        return; yield '';
    }

    // line 130
    public function block_form_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 131
        $context["widget_attr"] = [];
        // line 132
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 133
            $context["widget_attr"] = ["attr" => ["aria-describedby" => (($context["id"] ?? null) . "_help")]];
        }
        // line 135
        yield "<div";
        $__internal_compile_6 = $context;
        $__internal_compile_7 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 135)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 135), "")) : ("")) . " form-group") . (((( !($context["compound"] ?? null) || ((array_key_exists("force_error", $context)) ? (Twig\Extension\CoreExtension::default(($context["force_error"] ?? null), false)) : (false))) &&  !($context["valid"] ?? null))) ? (" has-error") : (""))))])];
        if (!is_iterable($__internal_compile_7)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 135, $this->getSourceContext());
        }
        $__internal_compile_7 = CoreExtension::toArray($__internal_compile_7);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_7));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_6;
        yield ">";
        // line 136
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label');
        yield " ";
        // line 137
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget', ($context["widget_attr"] ?? null));
        yield " ";
        // line 138
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'help');
        // line 139
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        yield " ";
        // line 140
        yield "</div> ";
        return; yield '';
    }

    // line 143
    public function block_button_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 144
        yield "<div";
        $__internal_compile_8 = $context;
        $__internal_compile_9 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 144)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 144), "")) : ("")) . " form-group"))])];
        if (!is_iterable($__internal_compile_9)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 144, $this->getSourceContext());
        }
        $__internal_compile_9 = CoreExtension::toArray($__internal_compile_9);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_9));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_8;
        yield ">";
        // line 145
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 146
        yield "</div>";
        return; yield '';
    }

    // line 149
    public function block_choice_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 150
        $context["force_error"] = true;
        // line 151
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 154
    public function block_date_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 155
        $context["force_error"] = true;
        // line 156
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 159
    public function block_time_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 160
        $context["force_error"] = true;
        // line 161
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 164
    public function block_datetime_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 165
        $context["force_error"] = true;
        // line 166
        yield from         $this->unwrap()->yieldBlock("form_row", $context, $blocks);
        return; yield '';
    }

    // line 169
    public function block_checkbox_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 170
        yield "<div";
        $__internal_compile_10 = $context;
        $__internal_compile_11 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 170)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 170), "")) : ("")) . " form-group") . (( !($context["valid"] ?? null)) ? (" has-error") : (""))))])];
        if (!is_iterable($__internal_compile_11)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 170, $this->getSourceContext());
        }
        $__internal_compile_11 = CoreExtension::toArray($__internal_compile_11);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_11));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_10;
        yield ">";
        // line 171
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 172
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'help');
        // line 173
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        // line 174
        yield "</div>";
        return; yield '';
    }

    // line 177
    public function block_radio_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 178
        yield "<div";
        $__internal_compile_12 = $context;
        $__internal_compile_13 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 178)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 178), "")) : ("")) . " form-group") . (( !($context["valid"] ?? null)) ? (" has-error") : (""))))])];
        if (!is_iterable($__internal_compile_13)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 178, $this->getSourceContext());
        }
        $__internal_compile_13 = CoreExtension::toArray($__internal_compile_13);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_13));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_12;
        yield ">";
        // line 179
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 180
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'help');
        // line 181
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        // line 182
        yield "</div>";
        return; yield '';
    }

    // line 187
    public function block_form_errors($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 188
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["errors"] ?? null)) > 0)) {
            // line 189
            if ( !Symfony\Bridge\Twig\Extension\twig_is_root_form(($context["form"] ?? null))) {
                yield "<span class=\"help-block\">";
            } else {
                yield "<div class=\"alert alert-danger\">";
            }
            // line 190
            yield "    <ul class=\"list-unstyled\">";
            // line 191
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["errors"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["error"]) {
                // line 192
                yield "<li><span class=\"glyphicon glyphicon-exclamation-sign\"></span> ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["error"], "message", [], "any", false, false, false, 192), "html", null, true);
                yield "</li>";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 194
            yield "</ul>
    ";
            // line 195
            if ( !Symfony\Bridge\Twig\Extension\twig_is_root_form(($context["form"] ?? null))) {
                yield "</span>";
            } else {
                yield "</div>";
            }
        }
        return; yield '';
    }

    // line 201
    public function block_form_help($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 202
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 203
            $context["help_attr"] = Twig\Extension\CoreExtension::merge(($context["help_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", true, true, false, 203)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", false, false, false, 203), "")) : ("")) . " help-block"))]);
            // line 204
            yield "<span id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "_help\"";
            $__internal_compile_14 = $context;
            $__internal_compile_15 = ["attr" => ($context["help_attr"] ?? null)];
            if (!is_iterable($__internal_compile_15)) {
                throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 204, $this->getSourceContext());
            }
            $__internal_compile_15 = CoreExtension::toArray($__internal_compile_15);
            $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_15));
            yield from             $this->unwrap()->yieldBlock("attributes", $context, $blocks);
            $context = $__internal_compile_14;
            yield ">";
            // line 205
            if ((($context["translation_domain"] ?? null) === false)) {
                // line 206
                if ((($context["help_html"] ?? null) === false)) {
                    // line 207
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["help"] ?? null), "html", null, true);
                } else {
                    // line 209
                    yield ($context["help"] ?? null);
                }
            } else {
                // line 212
                if ((($context["help_html"] ?? null) === false)) {
                    // line 213
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["help"] ?? null), ($context["help_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)), "html", null, true);
                } else {
                    // line 215
                    yield $this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["help"] ?? null), ($context["help_translation_parameters"] ?? null), ($context["translation_domain"] ?? null));
                }
            }
            // line 218
            yield "</span>";
        }
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "bootstrap_3_layout.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  674 => 218,  670 => 215,  667 => 213,  665 => 212,  661 => 209,  658 => 207,  656 => 206,  654 => 205,  640 => 204,  638 => 203,  636 => 202,  632 => 201,  622 => 195,  619 => 194,  611 => 192,  607 => 191,  605 => 190,  599 => 189,  597 => 188,  593 => 187,  588 => 182,  586 => 181,  584 => 180,  582 => 179,  570 => 178,  566 => 177,  561 => 174,  559 => 173,  557 => 172,  555 => 171,  543 => 170,  539 => 169,  534 => 166,  532 => 165,  528 => 164,  523 => 161,  521 => 160,  517 => 159,  512 => 156,  510 => 155,  506 => 154,  501 => 151,  499 => 150,  495 => 149,  490 => 146,  488 => 145,  476 => 144,  472 => 143,  467 => 140,  464 => 139,  462 => 138,  459 => 137,  456 => 136,  444 => 135,  441 => 133,  439 => 132,  437 => 131,  433 => 130,  427 => 124,  422 => 120,  419 => 118,  417 => 117,  413 => 114,  410 => 112,  408 => 111,  406 => 110,  402 => 109,  390 => 107,  386 => 104,  383 => 101,  382 => 100,  381 => 99,  379 => 98,  377 => 97,  374 => 95,  372 => 94,  370 => 93,  367 => 91,  365 => 90,  363 => 89,  359 => 87,  354 => 84,  352 => 82,  348 => 81,  343 => 78,  341 => 76,  337 => 75,  332 => 72,  330 => 71,  326 => 69,  321 => 66,  319 => 65,  315 => 64,  310 => 59,  308 => 58,  304 => 57,  298 => 53,  296 => 52,  294 => 51,  291 => 49,  289 => 48,  287 => 47,  283 => 46,  277 => 42,  275 => 41,  273 => 40,  270 => 38,  268 => 37,  266 => 36,  262 => 35,  256 => 31,  252 => 29,  246 => 27,  244 => 26,  242 => 25,  236 => 23,  234 => 22,  231 => 21,  228 => 20,  225 => 19,  223 => 18,  219 => 17,  214 => 14,  212 => 13,  208 => 12,  203 => 9,  200 => 7,  198 => 6,  194 => 5,  189 => 201,  186 => 200,  183 => 198,  181 => 187,  178 => 186,  175 => 184,  173 => 177,  170 => 176,  168 => 169,  165 => 168,  163 => 164,  160 => 163,  158 => 159,  155 => 158,  153 => 154,  150 => 153,  148 => 149,  145 => 148,  143 => 143,  140 => 142,  138 => 130,  135 => 129,  132 => 127,  130 => 87,  127 => 86,  125 => 81,  122 => 80,  120 => 75,  117 => 74,  115 => 69,  112 => 68,  110 => 64,  107 => 63,  104 => 61,  102 => 57,  99 => 56,  97 => 46,  94 => 45,  92 => 35,  89 => 34,  87 => 17,  84 => 16,  82 => 12,  79 => 11,  77 => 5,  74 => 4,  71 => 2,  31 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "bootstrap_3_layout.html.twig", "/var/www/html/vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_3_layout.html.twig");
    }
}
