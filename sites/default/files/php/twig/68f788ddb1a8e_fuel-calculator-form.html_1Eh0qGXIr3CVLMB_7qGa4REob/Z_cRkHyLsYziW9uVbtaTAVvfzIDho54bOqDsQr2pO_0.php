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
use Twig\TemplateWrapper;

/* modules/FuelCalculator/templates/fuel-calculator-form.html.twig */
class __TwigTemplate_0094fa866e80b968a8f6e2ecf6232c75 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("fuel_calculator/fuel_calculator_tailwind"), "html", null, true);
        yield "

<div class=\"max-w-6xl mx-auto p-6\">
  <div class=\"grid grid-cols-1 lg:grid-cols-2 gap-8\">

    <div class=\"bg-white rounded-lg shadow-lg p-6\">
      <h3 class=\"text-xl font-semibold text-gray-800 mb-6\">Enter Your Details</h3>

      <form";
        // line 9
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", ["space-y-6"], "method", false, false, true, 9), "html", null, true);
        yield " id=\"fuel-calculator-form\">

        <div class=\"space-y-2\">
          <div class=\"relative\">
            ";
        // line 13
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "distance", [], "any", false, false, true, 13), ["#attributes" => ["class" => ["block", "w-full", "px-3", "py-2", "border", "border-gray-300", "rounded-md", "shadow-sm", "placeholder-gray-400", "focus:outline-none", "focus:ring-2", "focus:ring-blue-500", "focus:border-blue-500", "sm:text-sm"], "placeholder" => "Enter distance in kilometers"]]), "html", null, true);
        // line 23
        yield "
            <div class=\"absolute inset-y-0 right-0 flex items-center pr-3\">
              <span class=\"text-gray-500 text-sm\">km</span>
            </div>
          </div>
          <div class=\"validation-message min-h-[20px] text-sm\"></div>
        </div>

        <div class=\"space-y-2\">
          <div class=\"relative\">
            ";
        // line 33
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "efficiency", [], "any", false, false, true, 33), ["#attributes" => ["class" => ["block", "w-full", "px-3", "py-2", "border", "border-gray-300", "rounded-md", "shadow-sm", "placeholder-gray-400", "focus:outline-none", "focus:ring-2", "focus:ring-blue-500", "focus:border-blue-500", "sm:text-sm"], "placeholder" => "Enter fuel efficiency"]]), "html", null, true);
        // line 43
        yield "
            <div class=\"absolute inset-y-0 right-0 flex items-center pr-3\">
              <span class=\"text-gray-500 text-sm\">L/100km</span>
            </div>
          </div>
          <div class=\"validation-message min-h-[20px] text-sm\"></div>
        </div>

        <div class=\"space-y-2\">
          <div class=\"relative\">
            ";
        // line 53
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "price", [], "any", false, false, true, 53), ["#attributes" => ["class" => ["block", "w-full", "px-3", "py-2", "border", "border-gray-300", "rounded-md", "shadow-sm", "placeholder-gray-400", "focus:outline-none", "focus:ring-2", "focus:ring-blue-500", "focus:border-blue-500", "sm:text-sm"], "placeholder" => "Enter fuel price"]]), "html", null, true);
        // line 63
        yield "
            <div class=\"absolute inset-y-0 right-0 flex items-center pr-3\">
              <span class=\"text-gray-500 text-sm\">EUR</span>
            </div>
          </div>
          <div class=\"validation-message min-h-[20px] text-sm\"></div>
        </div>

        <div class=\"flex flex-col sm:flex-row gap-3 pt-4\">
          ";
        // line 72
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "actions", [], "any", false, false, true, 72), "submit", [], "any", false, false, true, 72), ["#attributes" => ["class" => ["flex-1", "bg-blue-600", "hover:bg-blue-700", "text-white", "font-medium", "py-2", "px-4", "rounded-md", "focus:outline-none", "focus:ring-2", "focus:ring-blue-500", "focus:ring-offset-2", "transition", "duration-200", "disabled:opacity-50", "disabled:cursor-not-allowed"]]]), "html", null, true);
        // line 82
        yield "

          ";
        // line 84
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "actions", [], "any", false, false, true, 84), "reset", [], "any", false, false, true, 84), ["#attributes" => ["class" => ["flex-1", "bg-gray-600", "hover:bg-gray-700", "text-white", "font-medium", "py-2", "px-4", "rounded-md", "focus:outline-none", "focus:ring-2", "focus:ring-gray-500", "focus:ring-offset-2", "transition", "duration-200"]]]), "html", null, true);
        // line 93
        yield "
        </div>

        <div class=\"hidden\">
          ";
        // line 97
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(($context["form"] ?? null), "distance", "efficiency", "price", "actions", "results"), "html", null, true);
        yield "
        </div>
      </form>
    </div>

    <div class=\"bg-white rounded-lg shadow-lg p-6\">
      <h3 class=\"text-xl font-semibold text-gray-800 mb-6\">Calculation Results</h3>
      ";
        // line 104
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "results", [], "any", false, false, true, 104)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 105
            yield "        <div class=\"bg-green-50 border border-green-200 rounded-lg p-4\">
          <h4 class=\"text-lg font-semibold text-green-900 mb-4 flex items-center\">
            Final Results
          </h4>
          <div class=\"space-y-4\">
            <div class=\"bg-white rounded-md p-4 border border-green-300\">
              <div class=\"text-sm text-gray-600 mb-1\">Fuel Needed</div>
              <div class=\"text-2xl font-bold text-green-700\">";
            // line 112
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((($_v0 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "results", [], "any", false, false, true, 112), "spent", [], "any", false, false, true, 112)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#markup"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "results", [], "any", false, false, true, 112), "spent", [], "any", false, false, true, 112), "#markup", [], "array", false, false, true, 112)));
            yield "</div>
            </div>
            <div class=\"bg-white rounded-md p-4 border border-green-300\">
              <div class=\"text-sm text-gray-600 mb-1\">Total Cost</div>
              <div class=\"text-2xl font-bold text-green-700\">";
            // line 116
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((($_v1 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "results", [], "any", false, false, true, 116), "cost", [], "any", false, false, true, 116)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1["#markup"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "results", [], "any", false, false, true, 116), "cost", [], "any", false, false, true, 116), "#markup", [], "array", false, false, true, 116)));
            yield "</div>
            </div>
          </div>
        </div>
      ";
        }
        // line 121
        yield "
    </div>
  </div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["attributes", "form"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/FuelCalculator/templates/fuel-calculator-form.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  153 => 121,  145 => 116,  138 => 112,  129 => 105,  127 => 104,  117 => 97,  111 => 93,  109 => 84,  105 => 82,  103 => 72,  92 => 63,  90 => 53,  78 => 43,  76 => 33,  64 => 23,  62 => 13,  55 => 9,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/FuelCalculator/templates/fuel-calculator-form.html.twig", "/var/www/html/modules/FuelCalculator/templates/fuel-calculator-form.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 104];
        static $filters = ["escape" => 1, "merge" => 13, "without" => 97, "raw" => 112];
        static $functions = ["attach_library" => 1];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['escape', 'merge', 'without', 'raw'],
                ['attach_library'],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
