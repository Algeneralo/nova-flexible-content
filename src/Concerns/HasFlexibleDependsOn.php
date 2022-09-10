<?php

namespace Formfeed\NovaFlexibleContent\Concerns;

use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Formfeed\NovaFlexibleContent\Flexible;

trait HasFlexibleDependsOn {

    public function updateFields(NovaRequest $request) {
        return $this->parseFlexibleFields($request, parent::updateFields($request));
    }

    public function creationFields(NovaRequest $request) {
        return $this->parseFlexibleFields($request, parent::creationFields($request));
    }

    protected function parseFlexibleFields($request, $fields) {
        if ($request->getMethod() === "PATCH") {
            $fields = $this->flattenFields($fields);
        }
        return $fields;
    }

    protected function flattenFields($fields) {
        if ($fields instanceof FieldCollection) {
            $fields->each(function ($item, $key) use (&$fields) {
                if ($item instanceof Flexible) {
                    $item->resolve($this->resource, $item->attribute);
                    if (isset($item->meta['layouts'])) {
                        $item->meta['layouts']->each(function ($layout) use (&$fields) {
                            $fields = $fields->merge($this->flattenFields(new FieldCollection($layout->fields())));
                        });
                    }
                }
            });
        }
        return $fields;
    }
}
