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

/* bootstrap_5_layout.html.twig */
class __TwigTemplate_890bcca6aed1ea5bde862627546bfcb8 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        // line 1
        $_trait_0 = $this->loadTemplate("bootstrap_base_layout.html.twig", "bootstrap_5_layout.html.twig", 1);
        if (!$_trait_0->unwrap()->isTraitable()) {
            throw new RuntimeError('Template "'."bootstrap_base_layout.html.twig".'" cannot be used as a trait.', 1, $this->source);
        }
        $_trait_0_blocks = $_trait_0->unwrap()->getBlocks();

        $this->traits = $_trait_0_blocks;

        $this->blocks = array_merge(
            $this->traits,
            [
                'money_widget' => [$this, 'block_money_widget'],
                'date_widget' => [$this, 'block_date_widget'],
                'time_widget' => [$this, 'block_time_widget'],
                'datetime_widget' => [$this, 'block_datetime_widget'],
                'dateinterval_widget' => [$this, 'block_dateinterval_widget'],
                'percent_widget' => [$this, 'block_percent_widget'],
                'form_widget_simple' => [$this, 'block_form_widget_simple'],
                'widget_attributes' => [$this, 'block_widget_attributes'],
                'button_widget' => [$this, 'block_button_widget'],
                'submit_widget' => [$this, 'block_submit_widget'],
                'checkbox_widget' => [$this, 'block_checkbox_widget'],
                'radio_widget' => [$this, 'block_radio_widget'],
                'choice_widget_collapsed' => [$this, 'block_choice_widget_collapsed'],
                'choice_widget_expanded' => [$this, 'block_choice_widget_expanded'],
                'form_label' => [$this, 'block_form_label'],
                'checkbox_radio_label' => [$this, 'block_checkbox_radio_label'],
                'form_row' => [$this, 'block_form_row'],
                'button_row' => [$this, 'block_button_row'],
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
        yield from $this->unwrap()->yieldBlock('money_widget', $context, $blocks);
        // line 22
        yield "
";
        // line 23
        yield from $this->unwrap()->yieldBlock('date_widget', $context, $blocks);
        // line 53
        yield "
";
        // line 54
        yield from $this->unwrap()->yieldBlock('time_widget', $context, $blocks);
        // line 92
        yield "
";
        // line 93
        yield from $this->unwrap()->yieldBlock('datetime_widget', $context, $blocks);
        // line 109
        yield "
";
        // line 110
        yield from $this->unwrap()->yieldBlock('dateinterval_widget', $context, $blocks);
        // line 165
        yield "
";
        // line 166
        yield from $this->unwrap()->yieldBlock('percent_widget', $context, $blocks);
        // line 176
        yield "
";
        // line 177
        yield from $this->unwrap()->yieldBlock('form_widget_simple', $context, $blocks);
        // line 194
        yield from $this->unwrap()->yieldBlock('widget_attributes', $context, $blocks);
        // line 201
        yield from $this->unwrap()->yieldBlock('button_widget', $context, $blocks);
        // line 206
        yield from $this->unwrap()->yieldBlock('submit_widget', $context, $blocks);
        // line 211
        yield from $this->unwrap()->yieldBlock('checkbox_widget', $context, $blocks);
        // line 235
        yield from $this->unwrap()->yieldBlock('radio_widget', $context, $blocks);
        // line 256
        yield from $this->unwrap()->yieldBlock('choice_widget_collapsed', $context, $blocks);
        // line 261
        yield from $this->unwrap()->yieldBlock('choice_widget_expanded', $context, $blocks);
        // line 272
        yield "
";
        // line 275
        yield from $this->unwrap()->yieldBlock('form_label', $context, $blocks);
        // line 294
        yield from $this->unwrap()->yieldBlock('checkbox_radio_label', $context, $blocks);
        // line 320
        yield "
";
        // line 323
        yield from $this->unwrap()->yieldBlock('form_row', $context, $blocks);
        // line 345
        yield from $this->unwrap()->yieldBlock('button_row', $context, $blocks);
        // line 350
        yield "
";
        // line 353
        yield from $this->unwrap()->yieldBlock('form_errors', $context, $blocks);
        // line 360
        yield "
";
        // line 363
        yield from $this->unwrap()->yieldBlock('form_help', $context, $blocks);
        return; yield '';
    }

    // line 5
    public function block_money_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        $context["prepend"] =  !(is_string($__internal_compile_0 = ($context["money_pattern"] ?? null)) && is_string($__internal_compile_1 = "{{") && str_starts_with($__internal_compile_0, $__internal_compile_1));
        // line 7
        $context["append"] =  !(is_string($__internal_compile_2 = ($context["money_pattern"] ?? null)) && is_string($__internal_compile_3 = "}}") && str_ends_with($__internal_compile_2, $__internal_compile_3));
        // line 8
        if ((($context["prepend"] ?? null) || ($context["append"] ?? null))) {
            // line 9
            yield "<div class=\"input-group ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("group_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["group_class"] ?? null), "")) : ("")), "html", null, true);
            yield "\">";
            // line 10
            if (($context["prepend"] ?? null)) {
                // line 11
                yield "<span class=\"input-group-text\">";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->encodeCurrency($this->env, ($context["money_pattern"] ?? null));
                yield "</span>";
            }
            // line 13
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
            // line 14
            if (($context["append"] ?? null)) {
                // line 15
                yield "<span class=\"input-group-text\">";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->encodeCurrency($this->env, ($context["money_pattern"] ?? null));
                yield "</span>";
            }
            // line 17
            yield "</div>";
        } else {
            // line 19
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        }
        return; yield '';
    }

    // line 23
    public function block_date_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 24
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 25
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 27
            if ( !($context["valid"] ?? null)) {
                // line 28
                yield "            ";
                $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 28)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 28), "")) : ("")) . " is-invalid"))]);
                // line 29
                $context["valid"] = true;
                // line 30
                yield "        ";
            }
            // line 31
            if (( !array_key_exists("datetime", $context) ||  !($context["datetime"] ?? null))) {
                // line 32
                yield "<div ";
                yield from                 $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
                yield ">";
            }
            // line 34
            if ( !(($context["label"] ?? null) === false)) {
                // line 35
                yield "<div class=\"visually-hidden\">";
                // line 36
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "year", [], "any", false, false, false, 36), 'label');
                // line 37
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "month", [], "any", false, false, false, 37), 'label');
                // line 38
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "day", [], "any", false, false, false, 38), 'label');
                // line 39
                yield "</div>";
            }
            // line 41
            yield "<div class=\"input-group\">";
            // line 42
            yield Twig\Extension\CoreExtension::replace(($context["date_pattern"] ?? null), ["{{ year }}" =>             // line 43
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "year", [], "any", false, false, false, 43), 'widget'), "{{ month }}" =>             // line 44
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "month", [], "any", false, false, false, 44), 'widget'), "{{ day }}" =>             // line 45
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "day", [], "any", false, false, false, 45), 'widget')]);
            // line 47
            yield "</div>";
            // line 48
            if (( !array_key_exists("datetime", $context) ||  !($context["datetime"] ?? null))) {
                // line 49
                yield "</div>";
            }
        }
        return; yield '';
    }

    // line 54
    public function block_time_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 55
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 56
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 58
            if ( !($context["valid"] ?? null)) {
                // line 59
                yield "            ";
                $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 59)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 59), "")) : ("")) . " is-invalid"))]);
                // line 60
                $context["valid"] = true;
                // line 61
                yield "        ";
            }
            // line 62
            if (( !array_key_exists("datetime", $context) || (false == ($context["datetime"] ?? null)))) {
                // line 63
                yield "<div ";
                yield from                 $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
                yield ">";
            }
            // line 65
            if ( !(($context["label"] ?? null) === false)) {
                // line 66
                yield "<div class=\"visually-hidden\">";
                // line 67
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hour", [], "any", false, false, false, 67), 'label');
                // line 68
                if (($context["with_minutes"] ?? null)) {
                    yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minute", [], "any", false, false, false, 68), 'label');
                }
                // line 69
                if (($context["with_seconds"] ?? null)) {
                    yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "second", [], "any", false, false, false, 69), 'label');
                }
                // line 70
                yield "</div>";
            }
            // line 72
            if ((($context["with_minutes"] ?? null) || ($context["with_seconds"] ?? null))) {
                // line 73
                yield "            <div class=\"input-group\">
        ";
            }
            // line 75
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hour", [], "any", false, false, false, 75), 'widget');
            // line 76
            if (($context["with_minutes"] ?? null)) {
                // line 77
                yield "<span class=\"input-group-text\">:</span>";
                // line 78
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minute", [], "any", false, false, false, 78), 'widget');
            }
            // line 80
            if (($context["with_seconds"] ?? null)) {
                // line 81
                yield "<span class=\"input-group-text\">:</span>";
                // line 82
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "second", [], "any", false, false, false, 82), 'widget');
            }
            // line 84
            if ((($context["with_minutes"] ?? null) || ($context["with_seconds"] ?? null))) {
                // line 85
                yield "            </div>
        ";
            }
            // line 87
            if (( !array_key_exists("datetime", $context) || (false == ($context["datetime"] ?? null)))) {
                // line 88
                yield "</div>";
            }
        }
        return; yield '';
    }

    // line 93
    public function block_datetime_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 94
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 95
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 97
            if ( !($context["valid"] ?? null)) {
                // line 98
                yield "            ";
                $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 98)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 98), "")) : ("")) . " is-invalid"))]);
                // line 99
                $context["valid"] = true;
                // line 100
                yield "        ";
            }
            // line 101
            yield "        <div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">";
            // line 102
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "date", [], "any", false, false, false, 102), 'widget', ["datetime" => true]);
            // line 103
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "date", [], "any", false, false, false, 103), 'errors');
            // line 104
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "time", [], "any", false, false, false, 104), 'widget', ["datetime" => true]);
            // line 105
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "time", [], "any", false, false, false, 105), 'errors');
            // line 106
            yield "</div>";
        }
        return; yield '';
    }

    // line 110
    public function block_dateinterval_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 111
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 112
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 114
            if ( !($context["valid"] ?? null)) {
                // line 115
                yield "            ";
                $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 115)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 115), "")) : ("")) . " is-invalid"))]);
                // line 116
                $context["valid"] = true;
                // line 117
                yield "        ";
            }
            // line 118
            yield "        <div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">";
            // line 119
            if (($context["with_years"] ?? null)) {
                // line 120
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 121
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "years", [], "any", false, false, false, 121), 'label');
                yield "
                    ";
                // line 122
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "years", [], "any", false, false, false, 122), 'widget');
                yield "
                </div>";
            }
            // line 125
            if (($context["with_months"] ?? null)) {
                // line 126
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 127
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "months", [], "any", false, false, false, 127), 'label');
                yield "
                    ";
                // line 128
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "months", [], "any", false, false, false, 128), 'widget');
                yield "
                </div>";
            }
            // line 131
            if (($context["with_weeks"] ?? null)) {
                // line 132
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 133
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "weeks", [], "any", false, false, false, 133), 'label');
                yield "
                    ";
                // line 134
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "weeks", [], "any", false, false, false, 134), 'widget');
                yield "
                </div>";
            }
            // line 137
            if (($context["with_days"] ?? null)) {
                // line 138
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 139
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "days", [], "any", false, false, false, 139), 'label');
                yield "
                    ";
                // line 140
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "days", [], "any", false, false, false, 140), 'widget');
                yield "
                </div>";
            }
            // line 143
            if (($context["with_hours"] ?? null)) {
                // line 144
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 145
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hours", [], "any", false, false, false, 145), 'label');
                yield "
                    ";
                // line 146
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hours", [], "any", false, false, false, 146), 'widget');
                yield "
                </div>";
            }
            // line 149
            if (($context["with_minutes"] ?? null)) {
                // line 150
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 151
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minutes", [], "any", false, false, false, 151), 'label');
                yield "
                    ";
                // line 152
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minutes", [], "any", false, false, false, 152), 'widget');
                yield "
                </div>";
            }
            // line 155
            if (($context["with_seconds"] ?? null)) {
                // line 156
                yield "<div class=\"col-auto mb-3\">
                    ";
                // line 157
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "seconds", [], "any", false, false, false, 157), 'label');
                yield "
                    ";
                // line 158
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "seconds", [], "any", false, false, false, 158), 'widget');
                yield "
                </div>";
            }
            // line 161
            if (($context["with_invert"] ?? null)) {
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "invert", [], "any", false, false, false, 161), 'widget');
            }
            // line 162
            yield "</div>";
        }
        return; yield '';
    }

    // line 166
    public function block_percent_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 167
        if (($context["symbol"] ?? null)) {
            // line 168
            yield "<div class=\"input-group\">";
            // line 169
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
            // line 170
            yield "<span class=\"input-group-text\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("symbol", $context)) ? (Twig\Extension\CoreExtension::default(($context["symbol"] ?? null), "%")) : ("%")), "html", null, true);
            yield "</span>
        </div>";
        } else {
            // line 173
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        }
        return; yield '';
    }

    // line 177
    public function block_form_widget_simple($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 178
        if (( !array_key_exists("type", $context) || (($context["type"] ?? null) != "hidden"))) {
            // line 179
            $context["widget_class"] = " form-control";
            // line 180
            if ((((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "")) : ("")) == "color")) {
                // line 181
                $context["widget_class"] = (($context["widget_class"] ?? null) . " form-control-color");
            } elseif ((((            // line 182
array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "")) : ("")) == "range")) {
                // line 183
                $context["widget_class"] = " form-range";
            }
            // line 185
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 185)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 185), "")) : ("")) . ($context["widget_class"] ?? null)))]);
        }
        // line 187
        if ((array_key_exists("type", $context) && CoreExtension::inFilter(($context["type"] ?? null), ["range", "color"]))) {
            // line 188
            yield "        ";
            // line 189
            yield "        ";
            $context["required"] = false;
            // line 190
            yield "    ";
        }
        // line 191
        yield from $this->yieldParentBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 194
    public function block_widget_attributes($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 195
        if ( !($context["valid"] ?? null)) {
            // line 196
            yield "        ";
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 196)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 196), "")) : ("")) . " is-invalid"))]);
            // line 197
            yield "    ";
        }
        // line 198
        yield from $this->yieldParentBlock("widget_attributes", $context, $blocks);
        return; yield '';
    }

    // line 201
    public function block_button_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 202
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 202)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 202), "btn-secondary")) : ("btn-secondary")) . " btn"))]);
        // line 203
        yield from $this->yieldParentBlock("button_widget", $context, $blocks);
        return; yield '';
    }

    // line 206
    public function block_submit_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 207
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 207)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 207), "btn-primary")) : ("btn-primary")))]);
        // line 208
        yield from $this->yieldParentBlock("submit_widget", $context, $blocks);
        return; yield '';
    }

    // line 211
    public function block_checkbox_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 212
        $context["attr_class"] = ((array_key_exists("attr_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["attr_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 212)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 212), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 212)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 212), "")) : (""))));
        // line 213
        $context["row_class"] = "";
        // line 214
        if (!CoreExtension::inFilter("btn-check", ($context["attr_class"] ?? null))) {
            // line 215
            $context["attr_class"] = (($context["attr_class"] ?? null) . " form-check-input");
            // line 216
            $context["row_class"] = "form-check";
        }
        // line 218
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(($context["attr_class"] ?? null))]);
        // line 219
        $context["parent_label_class"] = ((array_key_exists("parent_label_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["parent_label_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 219)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 219), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 219)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 219), "")) : (""))));
        // line 220
        if (CoreExtension::inFilter("checkbox-inline", ($context["parent_label_class"] ?? null))) {
            // line 221
            $context["row_class"] = (($context["row_class"] ?? null) . " form-check-inline");
        }
        // line 223
        if (CoreExtension::inFilter("checkbox-switch", ($context["parent_label_class"] ?? null))) {
            // line 224
            $context["row_class"] = (($context["row_class"] ?? null) . " form-switch");
        }
        // line 226
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["row_class"] ?? null))) {
            // line 227
            yield "<div class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["row_class"] ?? null), "html", null, true);
            yield "\">";
        }
        // line 229
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("checkbox_widget", $context, $blocks)]);
        // line 230
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["row_class"] ?? null))) {
            // line 231
            yield "</div>";
        }
        return; yield '';
    }

    // line 235
    public function block_radio_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 236
        $context["attr_class"] = ((array_key_exists("attr_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["attr_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 236)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 236), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 236)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 236), "")) : (""))));
        // line 237
        $context["row_class"] = "";
        // line 238
        if (!CoreExtension::inFilter("btn-check", ($context["attr_class"] ?? null))) {
            // line 239
            $context["attr_class"] = (($context["attr_class"] ?? null) . " form-check-input");
            // line 240
            $context["row_class"] = "form-check";
        }
        // line 242
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(($context["attr_class"] ?? null))]);
        // line 243
        $context["parent_label_class"] = ((array_key_exists("parent_label_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["parent_label_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 243)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 243), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 243)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 243), "")) : (""))));
        // line 244
        if (CoreExtension::inFilter("radio-inline", ($context["parent_label_class"] ?? null))) {
            // line 245
            $context["row_class"] = (($context["row_class"] ?? null) . " form-check-inline");
        }
        // line 247
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["row_class"] ?? null))) {
            // line 248
            yield "<div class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["row_class"] ?? null), "html", null, true);
            yield "\">";
        }
        // line 250
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label', ["widget" => $this->renderParentBlock("radio_widget", $context, $blocks)]);
        // line 251
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["row_class"] ?? null))) {
            // line 252
            yield "</div>";
        }
        return; yield '';
    }

    // line 256
    public function block_choice_widget_collapsed($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 257
        $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", true, true, false, 257)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "class", [], "any", false, false, false, 257), "")) : ("")) . " form-select"))]);
        // line 258
        yield from $this->yieldParentBlock("choice_widget_collapsed", $context, $blocks);
        return; yield '';
    }

    // line 261
    public function block_choice_widget_expanded($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 262
        yield "<div ";
        yield from         $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
        yield ">";
        // line 263
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["form"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 264
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock($context["child"], 'widget', ["parent_label_class" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 265
($context["label_attr"] ?? null), "class", [], "any", true, true, false, 265)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 265), "")) : ("")), "translation_domain" =>             // line 266
($context["choice_translation_domain"] ?? null), "valid" =>             // line 267
($context["valid"] ?? null)]);
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 270
        yield "</div>";
        return; yield '';
    }

    // line 275
    public function block_form_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 276
        if ( !(($context["label"] ?? null) === false)) {
            // line 277
            $context["parent_label_class"] = ((array_key_exists("parent_label_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["parent_label_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 277)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 277), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 277)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 277), "")) : (""))));
            // line 278
            if ((array_key_exists("compound", $context) && ($context["compound"] ?? null))) {
                // line 279
                $context["element"] = "legend";
                // line 280
                if (!CoreExtension::inFilter("col-form-label", ($context["parent_label_class"] ?? null))) {
                    // line 281
                    $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 281)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 281), "")) : ("")) . " col-form-label"))]);
                }
            } else {
                // line 284
                $context["row_class"] = ((array_key_exists("row_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["row_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 284)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 284), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 284)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 284), "")) : (""))));
                // line 285
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["for" => ($context["id"] ?? null)]);
                // line 286
                if (!CoreExtension::inFilter("col-form-label", ($context["parent_label_class"] ?? null))) {
                    // line 287
                    $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 287)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 287), "")) : ("")) . ((CoreExtension::inFilter("input-group", ($context["row_class"] ?? null))) ? (" input-group-text") : (" form-label"))))]);
                }
            }
        }
        // line 291
        yield from $this->yieldParentBlock("form_label", $context, $blocks);
        return; yield '';
    }

    // line 294
    public function block_checkbox_radio_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 296
        if (array_key_exists("widget", $context)) {
            // line 297
            $context["label_attr_class"] = ((array_key_exists("label_attr_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["label_attr_class"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 297)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 297), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 297)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 297), "")) : (""))));
            // line 298
            if (!CoreExtension::inFilter("btn", ($context["label_attr_class"] ?? null))) {
                // line 299
                $context["label_attr_class"] = (($context["label_attr_class"] ?? null) . " form-check-label");
            }
            // line 301
            $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(($context["label_attr_class"] ?? null))]);
            // line 302
            if ( !($context["compound"] ?? null)) {
                // line 303
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["for" => ($context["id"] ?? null)]);
            }
            // line 305
            if (($context["required"] ?? null)) {
                // line 306
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 306)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 306), "")) : ("")) . " required"))]);
            }
            // line 308
            if (array_key_exists("parent_label_class", $context)) {
                // line 309
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::replace(((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 309)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 309), "")) : ("")) . " ") . ($context["parent_label_class"] ?? null)), ["checkbox-inline" => "", "radio-inline" => ""]))]);
            }
            // line 312
            yield ($context["widget"] ?? null);
            yield "
        <label";
            // line 313
            $__internal_compile_4 = $context;
            $__internal_compile_5 = ["attr" => ($context["label_attr"] ?? null)];
            if (!is_iterable($__internal_compile_5)) {
                throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 313, $this->getSourceContext());
            }
            $__internal_compile_5 = CoreExtension::toArray($__internal_compile_5);
            $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_5));
            yield from             $this->unwrap()->yieldBlock("attributes", $context, $blocks);
            $context = $__internal_compile_4;
            yield ">";
            // line 314
            if ( !(($context["label"] ?? null) === false)) {
                // line 315
                yield from                 $this->unwrap()->yieldBlock("form_label_content", $context, $blocks);
            }
            // line 317
            yield "</label>";
        }
        return; yield '';
    }

    // line 323
    public function block_form_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 324
        if ((array_key_exists("compound", $context) && ($context["compound"] ?? null))) {
            // line 325
            $context["element"] = "fieldset";
        }
        // line 327
        $context["widget_attr"] = [];
        // line 328
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 329
            $context["widget_attr"] = ["attr" => ["aria-describedby" => (($context["id"] ?? null) . "_help")]];
        }
        // line 331
        $context["row_class"] = ((array_key_exists("row_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["row_class"] ?? null), Twig\Extension\CoreExtension::trim(((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 331)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 331), "mb-3")) : ("mb-3"))))) : (Twig\Extension\CoreExtension::trim(((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 331)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 331), "mb-3")) : ("mb-3")))));
        // line 332
        yield "<";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "div")) : ("div")), "html", null, true);
        $__internal_compile_6 = $context;
        $__internal_compile_7 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => ($context["row_class"] ?? null)])];
        if (!is_iterable($__internal_compile_7)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 332, $this->getSourceContext());
        }
        $__internal_compile_7 = CoreExtension::toArray($__internal_compile_7);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_7));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_6;
        yield ">";
        // line 333
        if (CoreExtension::inFilter("form-floating", ($context["row_class"] ?? null))) {
            // line 334
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget', ($context["widget_attr"] ?? null));
            // line 335
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label');
        } else {
            // line 337
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label');
            // line 338
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget', ($context["widget_attr"] ?? null));
        }
        // line 340
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'help');
        // line 341
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        // line 342
        yield "</";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "div")) : ("div")), "html", null, true);
        yield ">";
        return; yield '';
    }

    // line 345
    public function block_button_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 346
        yield "<div";
        $__internal_compile_8 = $context;
        $__internal_compile_9 = ["attr" => Twig\Extension\CoreExtension::merge(($context["row_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 346)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 346), "mb-3")) : ("mb-3")))])];
        if (!is_iterable($__internal_compile_9)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 346, $this->getSourceContext());
        }
        $__internal_compile_9 = CoreExtension::toArray($__internal_compile_9);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_9));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_8;
        yield ">";
        // line 347
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 348
        yield "</div>";
        return; yield '';
    }

    // line 353
    public function block_form_errors($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 354
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["errors"] ?? null)) > 0)) {
            // line 355
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["errors"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["error"]) {
                // line 356
                yield "<div class=\"";
                if ( !Symfony\Bridge\Twig\Extension\twig_is_root_form(($context["form"] ?? null))) {
                    yield "invalid-feedback";
                } else {
                    yield "alert alert-danger";
                }
                yield " d-block\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["error"], "message", [], "any", false, false, false, 356), "html", null, true);
                yield "</div>";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        }
        return; yield '';
    }

    // line 363
    public function block_form_help($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 364
        $context["row_class"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", true, true, false, 364)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["row_attr"] ?? null), "class", [], "any", false, false, false, 364), "")) : (""));
        // line 365
        $context["help_class"] = " form-text";
        // line 366
        if (CoreExtension::inFilter("input-group", ($context["row_class"] ?? null))) {
            // line 368
            $context["help_class"] = " input-group-text";
        }
        // line 370
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 371
            $context["help_attr"] = Twig\Extension\CoreExtension::merge(($context["help_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim(((((CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", true, true, false, 371)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", false, false, false, 371), "")) : ("")) . ($context["help_class"] ?? null)) . " mb-0"))]);
        }
        // line 373
        yield from $this->yieldParentBlock("form_help", $context, $blocks);
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "bootstrap_5_layout.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  926 => 373,  923 => 371,  921 => 370,  918 => 368,  916 => 366,  914 => 365,  912 => 364,  908 => 363,  890 => 356,  886 => 355,  884 => 354,  880 => 353,  875 => 348,  873 => 347,  861 => 346,  857 => 345,  850 => 342,  848 => 341,  846 => 340,  843 => 338,  841 => 337,  838 => 335,  836 => 334,  834 => 333,  821 => 332,  819 => 331,  816 => 329,  814 => 328,  812 => 327,  809 => 325,  807 => 324,  803 => 323,  797 => 317,  794 => 315,  792 => 314,  781 => 313,  777 => 312,  774 => 309,  772 => 308,  769 => 306,  767 => 305,  764 => 303,  762 => 302,  760 => 301,  757 => 299,  755 => 298,  753 => 297,  751 => 296,  747 => 294,  742 => 291,  737 => 287,  735 => 286,  733 => 285,  731 => 284,  727 => 281,  725 => 280,  723 => 279,  721 => 278,  719 => 277,  717 => 276,  713 => 275,  708 => 270,  702 => 267,  701 => 266,  700 => 265,  699 => 264,  695 => 263,  691 => 262,  687 => 261,  682 => 258,  680 => 257,  676 => 256,  670 => 252,  668 => 251,  666 => 250,  661 => 248,  659 => 247,  656 => 245,  654 => 244,  652 => 243,  650 => 242,  647 => 240,  645 => 239,  643 => 238,  641 => 237,  639 => 236,  635 => 235,  629 => 231,  627 => 230,  625 => 229,  620 => 227,  618 => 226,  615 => 224,  613 => 223,  610 => 221,  608 => 220,  606 => 219,  604 => 218,  601 => 216,  599 => 215,  597 => 214,  595 => 213,  593 => 212,  589 => 211,  584 => 208,  582 => 207,  578 => 206,  573 => 203,  571 => 202,  567 => 201,  562 => 198,  559 => 197,  556 => 196,  554 => 195,  550 => 194,  545 => 191,  542 => 190,  539 => 189,  537 => 188,  535 => 187,  532 => 185,  529 => 183,  527 => 182,  525 => 181,  523 => 180,  521 => 179,  519 => 178,  515 => 177,  509 => 173,  503 => 170,  501 => 169,  499 => 168,  497 => 167,  493 => 166,  487 => 162,  483 => 161,  478 => 158,  474 => 157,  471 => 156,  469 => 155,  464 => 152,  460 => 151,  457 => 150,  455 => 149,  450 => 146,  446 => 145,  443 => 144,  441 => 143,  436 => 140,  432 => 139,  429 => 138,  427 => 137,  422 => 134,  418 => 133,  415 => 132,  413 => 131,  408 => 128,  404 => 127,  401 => 126,  399 => 125,  394 => 122,  390 => 121,  387 => 120,  385 => 119,  381 => 118,  378 => 117,  376 => 116,  373 => 115,  371 => 114,  368 => 112,  366 => 111,  362 => 110,  356 => 106,  354 => 105,  352 => 104,  350 => 103,  348 => 102,  344 => 101,  341 => 100,  339 => 99,  336 => 98,  334 => 97,  331 => 95,  329 => 94,  325 => 93,  318 => 88,  316 => 87,  312 => 85,  310 => 84,  307 => 82,  305 => 81,  303 => 80,  300 => 78,  298 => 77,  296 => 76,  294 => 75,  290 => 73,  288 => 72,  285 => 70,  281 => 69,  277 => 68,  275 => 67,  273 => 66,  271 => 65,  266 => 63,  264 => 62,  261 => 61,  259 => 60,  256 => 59,  254 => 58,  251 => 56,  249 => 55,  245 => 54,  238 => 49,  236 => 48,  234 => 47,  232 => 45,  231 => 44,  230 => 43,  229 => 42,  227 => 41,  224 => 39,  222 => 38,  220 => 37,  218 => 36,  216 => 35,  214 => 34,  209 => 32,  207 => 31,  204 => 30,  202 => 29,  199 => 28,  197 => 27,  194 => 25,  192 => 24,  188 => 23,  182 => 19,  179 => 17,  174 => 15,  172 => 14,  170 => 13,  165 => 11,  163 => 10,  159 => 9,  157 => 8,  155 => 7,  153 => 6,  149 => 5,  144 => 363,  141 => 360,  139 => 353,  136 => 350,  134 => 345,  132 => 323,  129 => 320,  127 => 294,  125 => 275,  122 => 272,  120 => 261,  118 => 256,  116 => 235,  114 => 211,  112 => 206,  110 => 201,  108 => 194,  106 => 177,  103 => 176,  101 => 166,  98 => 165,  96 => 110,  93 => 109,  91 => 93,  88 => 92,  86 => 54,  83 => 53,  81 => 23,  78 => 22,  76 => 5,  73 => 4,  70 => 2,  31 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "bootstrap_5_layout.html.twig", "/var/www/html/vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_layout.html.twig");
    }
}
