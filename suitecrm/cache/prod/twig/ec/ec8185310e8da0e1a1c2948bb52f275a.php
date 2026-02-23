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

/* form_div_layout.html.twig */
class __TwigTemplate_eb117269793ca9e024cc4b9b1bd20c8c extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'form_widget' => [$this, 'block_form_widget'],
            'form_widget_simple' => [$this, 'block_form_widget_simple'],
            'form_widget_compound' => [$this, 'block_form_widget_compound'],
            'collection_widget' => [$this, 'block_collection_widget'],
            'textarea_widget' => [$this, 'block_textarea_widget'],
            'choice_widget' => [$this, 'block_choice_widget'],
            'choice_widget_expanded' => [$this, 'block_choice_widget_expanded'],
            'choice_widget_collapsed' => [$this, 'block_choice_widget_collapsed'],
            'choice_widget_options' => [$this, 'block_choice_widget_options'],
            'checkbox_widget' => [$this, 'block_checkbox_widget'],
            'radio_widget' => [$this, 'block_radio_widget'],
            'datetime_widget' => [$this, 'block_datetime_widget'],
            'date_widget' => [$this, 'block_date_widget'],
            'time_widget' => [$this, 'block_time_widget'],
            'dateinterval_widget' => [$this, 'block_dateinterval_widget'],
            'number_widget' => [$this, 'block_number_widget'],
            'integer_widget' => [$this, 'block_integer_widget'],
            'money_widget' => [$this, 'block_money_widget'],
            'url_widget' => [$this, 'block_url_widget'],
            'search_widget' => [$this, 'block_search_widget'],
            'percent_widget' => [$this, 'block_percent_widget'],
            'password_widget' => [$this, 'block_password_widget'],
            'hidden_widget' => [$this, 'block_hidden_widget'],
            'email_widget' => [$this, 'block_email_widget'],
            'range_widget' => [$this, 'block_range_widget'],
            'button_widget' => [$this, 'block_button_widget'],
            'submit_widget' => [$this, 'block_submit_widget'],
            'reset_widget' => [$this, 'block_reset_widget'],
            'tel_widget' => [$this, 'block_tel_widget'],
            'color_widget' => [$this, 'block_color_widget'],
            'week_widget' => [$this, 'block_week_widget'],
            'form_label' => [$this, 'block_form_label'],
            'form_label_content' => [$this, 'block_form_label_content'],
            'button_label' => [$this, 'block_button_label'],
            'form_help' => [$this, 'block_form_help'],
            'form_help_content' => [$this, 'block_form_help_content'],
            'repeated_row' => [$this, 'block_repeated_row'],
            'form_row' => [$this, 'block_form_row'],
            'button_row' => [$this, 'block_button_row'],
            'hidden_row' => [$this, 'block_hidden_row'],
            'form' => [$this, 'block_form'],
            'form_start' => [$this, 'block_form_start'],
            'form_end' => [$this, 'block_form_end'],
            'form_errors' => [$this, 'block_form_errors'],
            'form_rest' => [$this, 'block_form_rest'],
            'form_rows' => [$this, 'block_form_rows'],
            'widget_attributes' => [$this, 'block_widget_attributes'],
            'widget_container_attributes' => [$this, 'block_widget_container_attributes'],
            'button_attributes' => [$this, 'block_button_attributes'],
            'attributes' => [$this, 'block_attributes'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 3
        yield from $this->unwrap()->yieldBlock('form_widget', $context, $blocks);
        // line 11
        yield from $this->unwrap()->yieldBlock('form_widget_simple', $context, $blocks);
        // line 20
        yield from $this->unwrap()->yieldBlock('form_widget_compound', $context, $blocks);
        // line 30
        yield from $this->unwrap()->yieldBlock('collection_widget', $context, $blocks);
        // line 37
        yield from $this->unwrap()->yieldBlock('textarea_widget', $context, $blocks);
        // line 41
        yield from $this->unwrap()->yieldBlock('choice_widget', $context, $blocks);
        // line 49
        yield from $this->unwrap()->yieldBlock('choice_widget_expanded', $context, $blocks);
        // line 58
        yield from $this->unwrap()->yieldBlock('choice_widget_collapsed', $context, $blocks);
        // line 80
        yield from $this->unwrap()->yieldBlock('choice_widget_options', $context, $blocks);
        // line 93
        yield from $this->unwrap()->yieldBlock('checkbox_widget', $context, $blocks);
        // line 97
        yield from $this->unwrap()->yieldBlock('radio_widget', $context, $blocks);
        // line 101
        yield from $this->unwrap()->yieldBlock('datetime_widget', $context, $blocks);
        // line 114
        yield from $this->unwrap()->yieldBlock('date_widget', $context, $blocks);
        // line 128
        yield from $this->unwrap()->yieldBlock('time_widget', $context, $blocks);
        // line 139
        yield from $this->unwrap()->yieldBlock('dateinterval_widget', $context, $blocks);
        // line 174
        yield from $this->unwrap()->yieldBlock('number_widget', $context, $blocks);
        // line 180
        yield from $this->unwrap()->yieldBlock('integer_widget', $context, $blocks);
        // line 185
        yield from $this->unwrap()->yieldBlock('money_widget', $context, $blocks);
        // line 189
        yield from $this->unwrap()->yieldBlock('url_widget', $context, $blocks);
        // line 194
        yield from $this->unwrap()->yieldBlock('search_widget', $context, $blocks);
        // line 199
        yield from $this->unwrap()->yieldBlock('percent_widget', $context, $blocks);
        // line 204
        yield from $this->unwrap()->yieldBlock('password_widget', $context, $blocks);
        // line 209
        yield from $this->unwrap()->yieldBlock('hidden_widget', $context, $blocks);
        // line 214
        yield from $this->unwrap()->yieldBlock('email_widget', $context, $blocks);
        // line 219
        yield from $this->unwrap()->yieldBlock('range_widget', $context, $blocks);
        // line 224
        yield from $this->unwrap()->yieldBlock('button_widget', $context, $blocks);
        // line 252
        yield from $this->unwrap()->yieldBlock('submit_widget', $context, $blocks);
        // line 257
        yield from $this->unwrap()->yieldBlock('reset_widget', $context, $blocks);
        // line 262
        yield from $this->unwrap()->yieldBlock('tel_widget', $context, $blocks);
        // line 267
        yield from $this->unwrap()->yieldBlock('color_widget', $context, $blocks);
        // line 272
        yield from $this->unwrap()->yieldBlock('week_widget', $context, $blocks);
        // line 285
        yield from $this->unwrap()->yieldBlock('form_label', $context, $blocks);
        // line 299
        yield from $this->unwrap()->yieldBlock('form_label_content', $context, $blocks);
        // line 325
        yield from $this->unwrap()->yieldBlock('button_label', $context, $blocks);
        // line 328
        yield "
";
        // line 329
        yield from $this->unwrap()->yieldBlock('form_help', $context, $blocks);
        // line 337
        yield "
";
        // line 338
        yield from $this->unwrap()->yieldBlock('form_help_content', $context, $blocks);
        // line 353
        yield "
";
        // line 356
        yield from $this->unwrap()->yieldBlock('repeated_row', $context, $blocks);
        // line 364
        yield from $this->unwrap()->yieldBlock('form_row', $context, $blocks);
        // line 377
        yield from $this->unwrap()->yieldBlock('button_row', $context, $blocks);
        // line 383
        yield from $this->unwrap()->yieldBlock('hidden_row', $context, $blocks);
        // line 389
        yield from $this->unwrap()->yieldBlock('form', $context, $blocks);
        // line 395
        yield from $this->unwrap()->yieldBlock('form_start', $context, $blocks);
        // line 409
        yield from $this->unwrap()->yieldBlock('form_end', $context, $blocks);
        // line 416
        yield from $this->unwrap()->yieldBlock('form_errors', $context, $blocks);
        // line 426
        yield from $this->unwrap()->yieldBlock('form_rest', $context, $blocks);
        // line 447
        yield "
";
        // line 450
        yield from $this->unwrap()->yieldBlock('form_rows', $context, $blocks);
        // line 456
        yield from $this->unwrap()->yieldBlock('widget_attributes', $context, $blocks);
        // line 463
        yield from $this->unwrap()->yieldBlock('widget_container_attributes', $context, $blocks);
        // line 468
        yield from $this->unwrap()->yieldBlock('button_attributes', $context, $blocks);
        // line 473
        yield from $this->unwrap()->yieldBlock('attributes', $context, $blocks);
        return; yield '';
    }

    // line 3
    public function block_form_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 4
        if (($context["compound"] ?? null)) {
            // line 5
            yield from             $this->unwrap()->yieldBlock("form_widget_compound", $context, $blocks);
        } else {
            // line 7
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        }
        return; yield '';
    }

    // line 11
    public function block_form_widget_simple($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 12
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "text")) : ("text"));
        // line 13
        if (((($context["type"] ?? null) == "range") || (($context["type"] ?? null) == "color"))) {
            // line 15
            $context["required"] = false;
        }
        // line 17
        yield "<input type=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["type"] ?? null), "html", null, true);
        yield "\" ";
        yield from         $this->unwrap()->yieldBlock("widget_attributes", $context, $blocks);
        yield " ";
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["value"] ?? null))) {
            yield "value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
            yield "\" ";
        }
        yield "/>";
        return; yield '';
    }

    // line 20
    public function block_form_widget_compound($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 21
        yield "<div ";
        yield from         $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
        yield ">";
        // line 22
        if (Symfony\Bridge\Twig\Extension\twig_is_root_form(($context["form"] ?? null))) {
            // line 23
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        }
        // line 25
        yield from         $this->unwrap()->yieldBlock("form_rows", $context, $blocks);
        // line 26
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'rest');
        // line 27
        yield "</div>";
        return; yield '';
    }

    // line 30
    public function block_collection_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 31
        if ((array_key_exists("prototype", $context) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["prototype"] ?? null), "rendered", [], "any", false, false, false, 31))) {
            // line 32
            $context["attr"] = Twig\Extension\CoreExtension::merge(($context["attr"] ?? null), ["data-prototype" => $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["prototype"] ?? null), 'row')]);
        }
        // line 34
        yield from         $this->unwrap()->yieldBlock("form_widget", $context, $blocks);
        return; yield '';
    }

    // line 37
    public function block_textarea_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 38
        yield "<textarea ";
        yield from         $this->unwrap()->yieldBlock("widget_attributes", $context, $blocks);
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
        yield "</textarea>";
        return; yield '';
    }

    // line 41
    public function block_choice_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 42
        if (($context["expanded"] ?? null)) {
            // line 43
            yield from             $this->unwrap()->yieldBlock("choice_widget_expanded", $context, $blocks);
        } else {
            // line 45
            yield from             $this->unwrap()->yieldBlock("choice_widget_collapsed", $context, $blocks);
        }
        return; yield '';
    }

    // line 49
    public function block_choice_widget_expanded($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 50
        yield "<div ";
        yield from         $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
        yield ">";
        // line 51
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["form"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 52
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock($context["child"], 'widget');
            // line 53
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock($context["child"], 'label', ["translation_domain" => ($context["choice_translation_domain"] ?? null)]);
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 55
        yield "</div>";
        return; yield '';
    }

    // line 58
    public function block_choice_widget_collapsed($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 59
        if (((((($context["required"] ?? null) && (null === ($context["placeholder"] ?? null))) &&  !($context["placeholder_in_choices"] ?? null)) &&  !($context["multiple"] ?? null)) && ( !CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "size", [], "any", true, true, false, 59) || (CoreExtension::getAttribute($this->env, $this->source, ($context["attr"] ?? null), "size", [], "any", false, false, false, 59) <= 1)))) {
            // line 60
            $context["required"] = false;
        }
        // line 62
        yield "<select ";
        yield from         $this->unwrap()->yieldBlock("widget_attributes", $context, $blocks);
        if (($context["multiple"] ?? null)) {
            yield " multiple=\"multiple\"";
        }
        yield ">";
        // line 63
        if ( !(null === ($context["placeholder"] ?? null))) {
            // line 64
            yield "<option value=\"\"";
            if (((array_key_exists("placeholder_attr", $context)) ? (Twig\Extension\CoreExtension::default(($context["placeholder_attr"] ?? null), [])) : ([]))) {
                $__internal_compile_0 = $context;
                $__internal_compile_1 = ["attr" => ($context["placeholder_attr"] ?? null)];
                if (!is_iterable($__internal_compile_1)) {
                    throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 64, $this->getSourceContext());
                }
                $__internal_compile_1 = CoreExtension::toArray($__internal_compile_1);
                $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_1));
                yield from                 $this->unwrap()->yieldBlock("attributes", $context, $blocks);
                $context = $__internal_compile_0;
            }
            if ((($context["required"] ?? null) && Twig\Extension\CoreExtension::testEmpty(($context["value"] ?? null)))) {
                yield " selected=\"selected\"";
            }
            yield ">";
            (((($context["placeholder"] ?? null) != "")) ? (yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["translation_domain"] ?? null) === false)) ? (($context["placeholder"] ?? null)) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["placeholder"] ?? null), [], ($context["translation_domain"] ?? null)))), "html", null, true)) : (yield ""));
            yield "</option>";
        }
        // line 66
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["preferred_choices"] ?? null)) > 0)) {
            // line 67
            $context["options"] = ($context["preferred_choices"] ?? null);
            // line 68
            yield "            ";
            $context["render_preferred_choices"] = true;
            // line 69
            yield from             $this->unwrap()->yieldBlock("choice_widget_options", $context, $blocks);
            // line 70
            if (((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["choices"] ?? null)) > 0) &&  !(null === ($context["separator"] ?? null)))) {
                // line 71
                yield "<option disabled=\"disabled\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["separator"] ?? null), "html", null, true);
                yield "</option>";
            }
        }
        // line 74
        $context["options"] = ($context["choices"] ?? null);
        // line 75
        $context["render_preferred_choices"] = false;
        // line 76
        yield from         $this->unwrap()->yieldBlock("choice_widget_options", $context, $blocks);
        // line 77
        yield "</select>";
        return; yield '';
    }

    // line 80
    public function block_choice_widget_options($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 81
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["options"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["group_label"] => $context["choice"]) {
            // line 82
            if (is_iterable($context["choice"])) {
                // line 83
                yield "<optgroup label=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["choice_translation_domain"] ?? null) === false)) ? ($context["group_label"]) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans($context["group_label"], [], ($context["choice_translation_domain"] ?? null)))), "html", null, true);
                yield "\">
                ";
                // line 84
                $context["options"] = $context["choice"];
                // line 85
                yield from                 $this->unwrap()->yieldBlock("choice_widget_options", $context, $blocks);
                // line 86
                yield "</optgroup>";
            } else {
                // line 88
                yield "<option value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "value", [], "any", false, false, false, 88), "html", null, true);
                yield "\"";
                if (CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "attr", [], "any", false, false, false, 88)) {
                    $__internal_compile_2 = $context;
                    $__internal_compile_3 = ["attr" => CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "attr", [], "any", false, false, false, 88)];
                    if (!is_iterable($__internal_compile_3)) {
                        throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 88, $this->getSourceContext());
                    }
                    $__internal_compile_3 = CoreExtension::toArray($__internal_compile_3);
                    $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_3));
                    yield from                     $this->unwrap()->yieldBlock("attributes", $context, $blocks);
                    $context = $__internal_compile_2;
                }
                if (( !((array_key_exists("render_preferred_choices", $context)) ? (Twig\Extension\CoreExtension::default(($context["render_preferred_choices"] ?? null), false)) : (false)) && Symfony\Bridge\Twig\Extension\twig_is_selected_choice($context["choice"], ($context["value"] ?? null)))) {
                    yield " selected=\"selected\"";
                }
                yield ">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($context["choice_translation_domain"] ?? null) === false)) ? (CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "label", [], "any", false, false, false, 88)) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "label", [], "any", false, false, false, 88), CoreExtension::getAttribute($this->env, $this->source, $context["choice"], "labelTranslationParameters", [], "any", false, false, false, 88), ($context["choice_translation_domain"] ?? null)))), "html", null, true);
                yield "</option>";
            }
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['group_label'], $context['choice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        return; yield '';
    }

    // line 93
    public function block_checkbox_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 94
        yield "<input type=\"checkbox\" ";
        yield from         $this->unwrap()->yieldBlock("widget_attributes", $context, $blocks);
        if (array_key_exists("value", $context)) {
            yield " value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
            yield "\"";
        }
        if (($context["checked"] ?? null)) {
            yield " checked=\"checked\"";
        }
        yield " />";
        return; yield '';
    }

    // line 97
    public function block_radio_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 98
        yield "<input type=\"radio\" ";
        yield from         $this->unwrap()->yieldBlock("widget_attributes", $context, $blocks);
        if (array_key_exists("value", $context)) {
            yield " value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
            yield "\"";
        }
        if (($context["checked"] ?? null)) {
            yield " checked=\"checked\"";
        }
        yield " />";
        return; yield '';
    }

    // line 101
    public function block_datetime_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 102
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 103
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 105
            yield "<div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">";
            // line 106
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "date", [], "any", false, false, false, 106), 'errors');
            // line 107
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "time", [], "any", false, false, false, 107), 'errors');
            // line 108
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "date", [], "any", false, false, false, 108), 'widget');
            // line 109
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "time", [], "any", false, false, false, 109), 'widget');
            // line 110
            yield "</div>";
        }
        return; yield '';
    }

    // line 114
    public function block_date_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 115
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 116
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 118
            yield "<div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">";
            // line 119
            yield Twig\Extension\CoreExtension::replace(($context["date_pattern"] ?? null), ["{{ year }}" =>             // line 120
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "year", [], "any", false, false, false, 120), 'widget'), "{{ month }}" =>             // line 121
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "month", [], "any", false, false, false, 121), 'widget'), "{{ day }}" =>             // line 122
$this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "day", [], "any", false, false, false, 122), 'widget')]);
            // line 124
            yield "</div>";
        }
        return; yield '';
    }

    // line 128
    public function block_time_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 129
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 130
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 132
            $context["vars"] = (((($context["widget"] ?? null) == "text")) ? (["attr" => ["size" => 1]]) : ([]));
            // line 133
            yield "<div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">
            ";
            // line 134
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hour", [], "any", false, false, false, 134), 'widget', ($context["vars"] ?? null));
            if (($context["with_minutes"] ?? null)) {
                yield ":";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minute", [], "any", false, false, false, 134), 'widget', ($context["vars"] ?? null));
            }
            if (($context["with_seconds"] ?? null)) {
                yield ":";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "second", [], "any", false, false, false, 134), 'widget', ($context["vars"] ?? null));
            }
            // line 135
            yield "        </div>";
        }
        return; yield '';
    }

    // line 139
    public function block_dateinterval_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 140
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 141
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 143
            yield "<div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">";
            // line 144
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
            // line 145
            yield "<table class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("table_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["table_class"] ?? null), "")) : ("")), "html", null, true);
            yield "\" role=\"presentation\">
                <thead>
                    <tr>";
            // line 148
            if (($context["with_years"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "years", [], "any", false, false, false, 148), 'label');
                yield "</th>";
            }
            // line 149
            if (($context["with_months"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "months", [], "any", false, false, false, 149), 'label');
                yield "</th>";
            }
            // line 150
            if (($context["with_weeks"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "weeks", [], "any", false, false, false, 150), 'label');
                yield "</th>";
            }
            // line 151
            if (($context["with_days"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "days", [], "any", false, false, false, 151), 'label');
                yield "</th>";
            }
            // line 152
            if (($context["with_hours"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hours", [], "any", false, false, false, 152), 'label');
                yield "</th>";
            }
            // line 153
            if (($context["with_minutes"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minutes", [], "any", false, false, false, 153), 'label');
                yield "</th>";
            }
            // line 154
            if (($context["with_seconds"] ?? null)) {
                yield "<th>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "seconds", [], "any", false, false, false, 154), 'label');
                yield "</th>";
            }
            // line 155
            yield "</tr>
                </thead>
                <tbody>
                    <tr>";
            // line 159
            if (($context["with_years"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "years", [], "any", false, false, false, 159), 'widget');
                yield "</td>";
            }
            // line 160
            if (($context["with_months"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "months", [], "any", false, false, false, 160), 'widget');
                yield "</td>";
            }
            // line 161
            if (($context["with_weeks"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "weeks", [], "any", false, false, false, 161), 'widget');
                yield "</td>";
            }
            // line 162
            if (($context["with_days"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "days", [], "any", false, false, false, 162), 'widget');
                yield "</td>";
            }
            // line 163
            if (($context["with_hours"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "hours", [], "any", false, false, false, 163), 'widget');
                yield "</td>";
            }
            // line 164
            if (($context["with_minutes"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "minutes", [], "any", false, false, false, 164), 'widget');
                yield "</td>";
            }
            // line 165
            if (($context["with_seconds"] ?? null)) {
                yield "<td>";
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "seconds", [], "any", false, false, false, 165), 'widget');
                yield "</td>";
            }
            // line 166
            yield "</tr>
                </tbody>
            </table>";
            // line 169
            if (($context["with_invert"] ?? null)) {
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "invert", [], "any", false, false, false, 169), 'widget');
            }
            // line 170
            yield "</div>";
        }
        return; yield '';
    }

    // line 174
    public function block_number_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 176
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "text")) : ("text"));
        // line 177
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 180
    public function block_integer_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 181
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "number")) : ("number"));
        // line 182
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 185
    public function block_money_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 186
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->encodeCurrency($this->env, ($context["money_pattern"] ?? null),         $this->unwrap()->renderBlock("form_widget_simple", $context, $blocks));
        return; yield '';
    }

    // line 189
    public function block_url_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 190
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "url")) : ("url"));
        // line 191
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 194
    public function block_search_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 195
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "search")) : ("search"));
        // line 196
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 199
    public function block_percent_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 200
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "text")) : ("text"));
        // line 201
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        if (($context["symbol"] ?? null)) {
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("symbol", $context)) ? (Twig\Extension\CoreExtension::default(($context["symbol"] ?? null), "%")) : ("%")), "html", null, true);
        }
        return; yield '';
    }

    // line 204
    public function block_password_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 205
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "password")) : ("password"));
        // line 206
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 209
    public function block_hidden_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 210
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "hidden")) : ("hidden"));
        // line 211
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 214
    public function block_email_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 215
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "email")) : ("email"));
        // line 216
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 219
    public function block_range_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 220
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "range")) : ("range"));
        // line 221
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 224
    public function block_button_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 225
        if (Twig\Extension\CoreExtension::testEmpty(($context["label"] ?? null))) {
            // line 226
            if ( !Twig\Extension\CoreExtension::testEmpty(($context["label_format"] ?? null))) {
                // line 227
                $context["label"] = Twig\Extension\CoreExtension::replace(($context["label_format"] ?? null), ["%name%" =>                 // line 228
($context["name"] ?? null), "%id%" =>                 // line 229
($context["id"] ?? null)]);
            } elseif ( !(            // line 231
($context["label"] ?? null) === false)) {
                // line 232
                $context["label"] = $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->humanize(($context["name"] ?? null));
            }
        }
        // line 235
        yield "<button type=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "button")) : ("button")), "html", null, true);
        yield "\" ";
        yield from         $this->unwrap()->yieldBlock("button_attributes", $context, $blocks);
        yield ">";
        // line 236
        if ((($context["translation_domain"] ?? null) === false)) {
            // line 237
            if ((($context["label_html"] ?? null) === false)) {
                // line 238
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
            } else {
                // line 240
                yield ($context["label"] ?? null);
            }
        } else {
            // line 243
            if ((($context["label_html"] ?? null) === false)) {
                // line 244
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)), "html", null, true);
            } else {
                // line 246
                yield $this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null));
            }
        }
        // line 249
        yield "</button>";
        return; yield '';
    }

    // line 252
    public function block_submit_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 253
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "submit")) : ("submit"));
        // line 254
        yield from         $this->unwrap()->yieldBlock("button_widget", $context, $blocks);
        return; yield '';
    }

    // line 257
    public function block_reset_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 258
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "reset")) : ("reset"));
        // line 259
        yield from         $this->unwrap()->yieldBlock("button_widget", $context, $blocks);
        return; yield '';
    }

    // line 262
    public function block_tel_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 263
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "tel")) : ("tel"));
        // line 264
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 267
    public function block_color_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 268
        $context["type"] = ((array_key_exists("type", $context)) ? (Twig\Extension\CoreExtension::default(($context["type"] ?? null), "color")) : ("color"));
        // line 269
        yield from         $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        return; yield '';
    }

    // line 272
    public function block_week_widget($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 273
        if ((($context["widget"] ?? null) == "single_text")) {
            // line 274
            yield from             $this->unwrap()->yieldBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 276
            $context["vars"] = (((($context["widget"] ?? null) == "text")) ? (["attr" => ["size" => 1]]) : ([]));
            // line 277
            yield "<div ";
            yield from             $this->unwrap()->yieldBlock("widget_container_attributes", $context, $blocks);
            yield ">
            ";
            // line 278
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "year", [], "any", false, false, false, 278), 'widget', ($context["vars"] ?? null));
            yield "-";
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "week", [], "any", false, false, false, 278), 'widget', ($context["vars"] ?? null));
            yield "
        </div>";
        }
        return; yield '';
    }

    // line 285
    public function block_form_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 286
        if ( !(($context["label"] ?? null) === false)) {
            // line 287
            if ( !($context["compound"] ?? null)) {
                // line 288
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["for" => ($context["id"] ?? null)]);
            }
            // line 290
            if (($context["required"] ?? null)) {
                // line 291
                $context["label_attr"] = Twig\Extension\CoreExtension::merge(($context["label_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", true, true, false, 291)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["label_attr"] ?? null), "class", [], "any", false, false, false, 291), "")) : ("")) . " required"))]);
            }
            // line 293
            yield "<";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "label")) : ("label")), "html", null, true);
            if (($context["label_attr"] ?? null)) {
                $__internal_compile_4 = $context;
                $__internal_compile_5 = ["attr" => ($context["label_attr"] ?? null)];
                if (!is_iterable($__internal_compile_5)) {
                    throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 293, $this->getSourceContext());
                }
                $__internal_compile_5 = CoreExtension::toArray($__internal_compile_5);
                $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_5));
                yield from                 $this->unwrap()->yieldBlock("attributes", $context, $blocks);
                $context = $__internal_compile_4;
            }
            yield ">";
            // line 294
            yield from             $this->unwrap()->yieldBlock("form_label_content", $context, $blocks);
            // line 295
            yield "</";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "label")) : ("label")), "html", null, true);
            yield ">";
        }
        return; yield '';
    }

    // line 299
    public function block_form_label_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 300
        if (Twig\Extension\CoreExtension::testEmpty(($context["label"] ?? null))) {
            // line 301
            if ( !Twig\Extension\CoreExtension::testEmpty(($context["label_format"] ?? null))) {
                // line 302
                $context["label"] = Twig\Extension\CoreExtension::replace(($context["label_format"] ?? null), ["%name%" =>                 // line 303
($context["name"] ?? null), "%id%" =>                 // line 304
($context["id"] ?? null)]);
            } else {
                // line 307
                $context["label"] = $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->humanize(($context["name"] ?? null));
            }
        }
        // line 310
        if ((($context["translation_domain"] ?? null) === false)) {
            // line 311
            if ((($context["label_html"] ?? null) === false)) {
                // line 312
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
            } else {
                // line 314
                yield ($context["label"] ?? null);
            }
        } else {
            // line 317
            if ((($context["label_html"] ?? null) === false)) {
                // line 318
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)), "html", null, true);
            } else {
                // line 320
                yield $this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["label"] ?? null), ($context["label_translation_parameters"] ?? null), ($context["translation_domain"] ?? null));
            }
        }
        return; yield '';
    }

    // line 325
    public function block_button_label($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 329
    public function block_form_help($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 330
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 331
            $context["help_attr"] = Twig\Extension\CoreExtension::merge(($context["help_attr"] ?? null), ["class" => Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", true, true, false, 331)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["help_attr"] ?? null), "class", [], "any", false, false, false, 331), "")) : ("")) . " help-text"))]);
            // line 332
            yield "<";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "div")) : ("div")), "html", null, true);
            yield " id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "_help\"";
            $__internal_compile_6 = $context;
            $__internal_compile_7 = ["attr" => ($context["help_attr"] ?? null)];
            if (!is_iterable($__internal_compile_7)) {
                throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 332, $this->getSourceContext());
            }
            $__internal_compile_7 = CoreExtension::toArray($__internal_compile_7);
            $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_7));
            yield from             $this->unwrap()->yieldBlock("attributes", $context, $blocks);
            $context = $__internal_compile_6;
            yield ">";
            // line 333
            yield from             $this->unwrap()->yieldBlock("form_help_content", $context, $blocks);
            // line 334
            yield "</";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("element", $context)) ? (Twig\Extension\CoreExtension::default(($context["element"] ?? null), "div")) : ("div")), "html", null, true);
            yield ">";
        }
        return; yield '';
    }

    // line 338
    public function block_form_help_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 339
        if ((($context["translation_domain"] ?? null) === false)) {
            // line 340
            if ((($context["help_html"] ?? null) === false)) {
                // line 341
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["help"] ?? null), "html", null, true);
            } else {
                // line 343
                yield ($context["help"] ?? null);
            }
        } else {
            // line 346
            if ((($context["help_html"] ?? null) === false)) {
                // line 347
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["help"] ?? null), ($context["help_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)), "html", null, true);
            } else {
                // line 349
                yield $this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(($context["help"] ?? null), ($context["help_translation_parameters"] ?? null), ($context["translation_domain"] ?? null));
            }
        }
        return; yield '';
    }

    // line 356
    public function block_repeated_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 361
        yield from         $this->unwrap()->yieldBlock("form_rows", $context, $blocks);
        return; yield '';
    }

    // line 364
    public function block_form_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 365
        $context["widget_attr"] = [];
        // line 366
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["help"] ?? null))) {
            // line 367
            $context["widget_attr"] = ["attr" => ["aria-describedby" => (($context["id"] ?? null) . "_help")]];
        }
        // line 369
        yield "<div";
        $__internal_compile_8 = $context;
        $__internal_compile_9 = ["attr" => ($context["row_attr"] ?? null)];
        if (!is_iterable($__internal_compile_9)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 369, $this->getSourceContext());
        }
        $__internal_compile_9 = CoreExtension::toArray($__internal_compile_9);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_9));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_8;
        yield ">";
        // line 370
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'label');
        // line 371
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'errors');
        // line 372
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget', ($context["widget_attr"] ?? null));
        // line 373
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'help');
        // line 374
        yield "</div>";
        return; yield '';
    }

    // line 377
    public function block_button_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 378
        yield "<div";
        $__internal_compile_10 = $context;
        $__internal_compile_11 = ["attr" => ($context["row_attr"] ?? null)];
        if (!is_iterable($__internal_compile_11)) {
            throw new RuntimeError('Variables passed to the "with" tag must be a mapping.', 378, $this->getSourceContext());
        }
        $__internal_compile_11 = CoreExtension::toArray($__internal_compile_11);
        $context = $this->env->mergeGlobals(array_merge($context, $__internal_compile_11));
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        $context = $__internal_compile_10;
        yield ">";
        // line 379
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 380
        yield "</div>";
        return; yield '';
    }

    // line 383
    public function block_hidden_row($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 384
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        return; yield '';
    }

    // line 389
    public function block_form($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 390
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock(($context["form"] ?? null), 'form_start');
        // line 391
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'widget');
        // line 392
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock(($context["form"] ?? null), 'form_end');
        return; yield '';
    }

    // line 395
    public function block_form_start($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 396
        CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "setMethodRendered", [], "method", false, false, false, 396);
        // line 397
        $context["method"] = Twig\Extension\CoreExtension::upper($this->env->getCharset(), ($context["method"] ?? null));
        // line 398
        if (CoreExtension::inFilter(($context["method"] ?? null), ["GET", "POST"])) {
            // line 399
            $context["form_method"] = ($context["method"] ?? null);
        } else {
            // line 401
            $context["form_method"] = "POST";
        }
        // line 403
        yield "<form";
        if ((($context["name"] ?? null) != "")) {
            yield " name=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
            yield "\"";
        }
        yield " method=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::lower($this->env->getCharset(), ($context["form_method"] ?? null)), "html", null, true);
        yield "\"";
        if ((($context["action"] ?? null) != "")) {
            yield " action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["action"] ?? null), "html", null, true);
            yield "\"";
        }
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        if (($context["multipart"] ?? null)) {
            yield " enctype=\"multipart/form-data\"";
        }
        yield ">";
        // line 404
        if ((($context["form_method"] ?? null) != ($context["method"] ?? null))) {
            // line 405
            yield "<input type=\"hidden\" name=\"_method\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["method"] ?? null), "html", null, true);
            yield "\" />";
        }
        return; yield '';
    }

    // line 409
    public function block_form_end($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 410
        if (( !array_key_exists("render_rest", $context) || ($context["render_rest"] ?? null))) {
            // line 411
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(($context["form"] ?? null), 'rest');
        }
        // line 413
        yield "</form>";
        return; yield '';
    }

    // line 416
    public function block_form_errors($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 417
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["errors"] ?? null)) > 0)) {
            // line 418
            yield "<ul>";
            // line 419
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["errors"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["error"]) {
                // line 420
                yield "<li>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["error"], "message", [], "any", false, false, false, 420), "html", null, true);
                yield "</li>";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 422
            yield "</ul>";
        }
        return; yield '';
    }

    // line 426
    public function block_form_rest($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 427
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["form"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 428
            if ( !CoreExtension::getAttribute($this->env, $this->source, $context["child"], "rendered", [], "any", false, false, false, 428)) {
                // line 429
                yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock($context["child"], 'row');
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 433
        if (( !CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "methodRendered", [], "any", false, false, false, 433) && Symfony\Bridge\Twig\Extension\twig_is_root_form(($context["form"] ?? null)))) {
            // line 434
            CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "setMethodRendered", [], "method", false, false, false, 434);
            // line 435
            $context["method"] = Twig\Extension\CoreExtension::upper($this->env->getCharset(), ($context["method"] ?? null));
            // line 436
            if (CoreExtension::inFilter(($context["method"] ?? null), ["GET", "POST"])) {
                // line 437
                $context["form_method"] = ($context["method"] ?? null);
            } else {
                // line 439
                $context["form_method"] = "POST";
            }
            // line 442
            if ((($context["form_method"] ?? null) != ($context["method"] ?? null))) {
                // line 443
                yield "<input type=\"hidden\" name=\"_method\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["method"] ?? null), "html", null, true);
                yield "\" />";
            }
        }
        return; yield '';
    }

    // line 450
    public function block_form_rows($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 451
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::filter($this->env, ($context["form"] ?? null), function ($__child__) use ($context, $macros) { $context["child"] = $__child__; return  !CoreExtension::getAttribute($this->env, $this->source, $context["child"], "rendered", [], "any", false, false, false, 451); }));
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 452
            yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock($context["child"], 'row');
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        return; yield '';
    }

    // line 456
    public function block_widget_attributes($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 457
        yield "id=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
        yield "\" name=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["full_name"] ?? null), "html", null, true);
        yield "\"";
        // line 458
        if (($context["disabled"] ?? null)) {
            yield " disabled=\"disabled\"";
        }
        // line 459
        if (($context["required"] ?? null)) {
            yield " required=\"required\"";
        }
        // line 460
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        return; yield '';
    }

    // line 463
    public function block_widget_container_attributes($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 464
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["id"] ?? null))) {
            yield "id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\"";
        }
        // line 465
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        return; yield '';
    }

    // line 468
    public function block_button_attributes($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 469
        yield "id=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
        yield "\" name=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["full_name"] ?? null), "html", null, true);
        yield "\"";
        if (($context["disabled"] ?? null)) {
            yield " disabled=\"disabled\"";
        }
        // line 470
        yield from         $this->unwrap()->yieldBlock("attributes", $context, $blocks);
        return; yield '';
    }

    // line 473
    public function block_attributes($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 474
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["attr"] ?? null));
        foreach ($context['_seq'] as $context["attrname"] => $context["attrvalue"]) {
            // line 475
            yield " ";
            // line 476
            if (CoreExtension::inFilter($context["attrname"], ["placeholder", "title"])) {
                // line 477
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attrname"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((((($context["translation_domain"] ?? null) === false) || (null === $context["attrvalue"]))) ? ($context["attrvalue"]) : ($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans($context["attrvalue"], ($context["attr_translation_parameters"] ?? null), ($context["translation_domain"] ?? null)))), "html", null, true);
                yield "\"";
            } elseif ((            // line 478
$context["attrvalue"] === true)) {
                // line 479
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attrname"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attrname"], "html", null, true);
                yield "\"";
            } elseif ( !(            // line 480
$context["attrvalue"] === false)) {
                // line 481
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attrname"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attrvalue"], "html", null, true);
                yield "\"";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['attrname'], $context['attrvalue'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "form_div_layout.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  1413 => 481,  1411 => 480,  1406 => 479,  1404 => 478,  1399 => 477,  1397 => 476,  1395 => 475,  1391 => 474,  1387 => 473,  1382 => 470,  1373 => 469,  1369 => 468,  1364 => 465,  1358 => 464,  1354 => 463,  1349 => 460,  1345 => 459,  1341 => 458,  1335 => 457,  1331 => 456,  1322 => 452,  1318 => 451,  1314 => 450,  1305 => 443,  1303 => 442,  1300 => 439,  1297 => 437,  1295 => 436,  1293 => 435,  1291 => 434,  1289 => 433,  1282 => 429,  1280 => 428,  1276 => 427,  1272 => 426,  1266 => 422,  1258 => 420,  1254 => 419,  1252 => 418,  1250 => 417,  1246 => 416,  1241 => 413,  1238 => 411,  1236 => 410,  1232 => 409,  1224 => 405,  1222 => 404,  1202 => 403,  1199 => 401,  1196 => 399,  1194 => 398,  1192 => 397,  1190 => 396,  1186 => 395,  1181 => 392,  1179 => 391,  1177 => 390,  1173 => 389,  1168 => 384,  1164 => 383,  1159 => 380,  1157 => 379,  1145 => 378,  1141 => 377,  1136 => 374,  1134 => 373,  1132 => 372,  1130 => 371,  1128 => 370,  1116 => 369,  1113 => 367,  1111 => 366,  1109 => 365,  1105 => 364,  1100 => 361,  1096 => 356,  1089 => 349,  1086 => 347,  1084 => 346,  1080 => 343,  1077 => 341,  1075 => 340,  1073 => 339,  1069 => 338,  1061 => 334,  1059 => 333,  1043 => 332,  1041 => 331,  1039 => 330,  1035 => 329,  1028 => 325,  1021 => 320,  1018 => 318,  1016 => 317,  1012 => 314,  1009 => 312,  1007 => 311,  1005 => 310,  1001 => 307,  998 => 304,  997 => 303,  996 => 302,  994 => 301,  992 => 300,  988 => 299,  980 => 295,  978 => 294,  963 => 293,  960 => 291,  958 => 290,  955 => 288,  953 => 287,  951 => 286,  947 => 285,  937 => 278,  932 => 277,  930 => 276,  927 => 274,  925 => 273,  921 => 272,  916 => 269,  914 => 268,  910 => 267,  905 => 264,  903 => 263,  899 => 262,  894 => 259,  892 => 258,  888 => 257,  883 => 254,  881 => 253,  877 => 252,  872 => 249,  868 => 246,  865 => 244,  863 => 243,  859 => 240,  856 => 238,  854 => 237,  852 => 236,  846 => 235,  842 => 232,  840 => 231,  838 => 229,  837 => 228,  836 => 227,  834 => 226,  832 => 225,  828 => 224,  823 => 221,  821 => 220,  817 => 219,  812 => 216,  810 => 215,  806 => 214,  801 => 211,  799 => 210,  795 => 209,  790 => 206,  788 => 205,  784 => 204,  775 => 201,  773 => 200,  769 => 199,  764 => 196,  762 => 195,  758 => 194,  753 => 191,  751 => 190,  747 => 189,  742 => 186,  738 => 185,  733 => 182,  731 => 181,  727 => 180,  722 => 177,  720 => 176,  716 => 174,  710 => 170,  706 => 169,  702 => 166,  696 => 165,  690 => 164,  684 => 163,  678 => 162,  672 => 161,  666 => 160,  660 => 159,  655 => 155,  649 => 154,  643 => 153,  637 => 152,  631 => 151,  625 => 150,  619 => 149,  613 => 148,  607 => 145,  605 => 144,  601 => 143,  598 => 141,  596 => 140,  592 => 139,  586 => 135,  576 => 134,  571 => 133,  569 => 132,  566 => 130,  564 => 129,  560 => 128,  554 => 124,  552 => 122,  551 => 121,  550 => 120,  549 => 119,  545 => 118,  542 => 116,  540 => 115,  536 => 114,  530 => 110,  528 => 109,  526 => 108,  524 => 107,  522 => 106,  518 => 105,  515 => 103,  513 => 102,  509 => 101,  494 => 98,  490 => 97,  475 => 94,  471 => 93,  434 => 88,  431 => 86,  429 => 85,  427 => 84,  422 => 83,  420 => 82,  403 => 81,  399 => 80,  394 => 77,  392 => 76,  390 => 75,  388 => 74,  382 => 71,  380 => 70,  378 => 69,  375 => 68,  373 => 67,  371 => 66,  351 => 64,  349 => 63,  342 => 62,  339 => 60,  337 => 59,  333 => 58,  328 => 55,  322 => 53,  320 => 52,  316 => 51,  312 => 50,  308 => 49,  302 => 45,  299 => 43,  297 => 42,  293 => 41,  284 => 38,  280 => 37,  275 => 34,  272 => 32,  270 => 31,  266 => 30,  261 => 27,  259 => 26,  257 => 25,  254 => 23,  252 => 22,  248 => 21,  244 => 20,  229 => 17,  226 => 15,  224 => 13,  222 => 12,  218 => 11,  212 => 7,  209 => 5,  207 => 4,  203 => 3,  198 => 473,  196 => 468,  194 => 463,  192 => 456,  190 => 450,  187 => 447,  185 => 426,  183 => 416,  181 => 409,  179 => 395,  177 => 389,  175 => 383,  173 => 377,  171 => 364,  169 => 356,  166 => 353,  164 => 338,  161 => 337,  159 => 329,  156 => 328,  154 => 325,  152 => 299,  150 => 285,  148 => 272,  146 => 267,  144 => 262,  142 => 257,  140 => 252,  138 => 224,  136 => 219,  134 => 214,  132 => 209,  130 => 204,  128 => 199,  126 => 194,  124 => 189,  122 => 185,  120 => 180,  118 => 174,  116 => 139,  114 => 128,  112 => 114,  110 => 101,  108 => 97,  106 => 93,  104 => 80,  102 => 58,  100 => 49,  98 => 41,  96 => 37,  94 => 30,  92 => 20,  90 => 11,  88 => 3,);
    }

    public function getSourceContext()
    {
        return new Source("", "form_div_layout.html.twig", "/var/www/html/vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig");
    }
}
