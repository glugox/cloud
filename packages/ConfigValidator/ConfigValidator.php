<?php

namespace Cloud\Packages\ConfigValidator;

class ConfigValidator
{
    /**
     * Validate a module configuration array.
     *
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validate(array $config): array
    {
        $errors = [];

        if (! isset($config['app']) || ! is_array($config['app'])) {
            $errors[] = 'Missing app configuration.';
        } else {
            $app = $config['app'];
            foreach (['name', 'seedEnabled', 'seedCount'] as $field) {
                if (! array_key_exists($field, $app)) {
                    $errors[] = sprintf('Missing app.%s value.', $field);
                }
            }

            if (isset($app['seedCount']) && (! is_int($app['seedCount']) || $app['seedCount'] < 0)) {
                $errors[] = 'app.seedCount must be a non-negative integer.';
            }
        }

        if (! isset($config['entities']) || ! is_array($config['entities'])) {
            $errors[] = 'Entities must be an array.';
        } else {
            foreach ($config['entities'] as $index => $entity) {
                if (! is_array($entity)) {
                    $errors[] = sprintf('Entity at index %d must be an object.', $index);
                    continue;
                }

                foreach (['name', 'fields'] as $required) {
                    if (! array_key_exists($required, $entity)) {
                        $errors[] = sprintf('Entity %s missing required key: %s.', $entity['name'] ?? $index, $required);
                    }
                }

                if (isset($entity['fields']) && ! is_array($entity['fields'])) {
                    $errors[] = sprintf('Entity %s fields must be an array.', $entity['name'] ?? $index);
                    continue;
                }

                if (isset($entity['fields']) && is_array($entity['fields'])) {
                    foreach ($entity['fields'] as $fieldIndex => $field) {
                        if (! is_array($field)) {
                            $errors[] = sprintf('Field at index %d in entity %s must be an object.', $fieldIndex, $entity['name'] ?? $index);
                            continue;
                        }

                        foreach (['name', 'type'] as $fieldRequired) {
                            if (! array_key_exists($fieldRequired, $field)) {
                                $errors[] = sprintf(
                                    'Field %s in entity %s missing required key: %s.',
                                    $field['name'] ?? $fieldIndex,
                                    $entity['name'] ?? $index,
                                    $fieldRequired
                                );
                            }
                        }
                    }
                }
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
        ];
    }
}
