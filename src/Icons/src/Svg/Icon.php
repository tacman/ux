<?php

namespace Symfony\UX\Icons\Svg;

/**
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class Icon implements \Stringable, \Serializable, \ArrayAccess
{
    /**
     * Transforms a valid icon ID into an icon name.
     *
     * @throws \InvalidArgumentException if the ID is not valid
     * @see isValidId()
     */
    public static function idToName(string $id): string
    {
        if (!self::isValidId($id)) {
            throw new \InvalidArgumentException(sprintf('The id "%s" is not a valid id.', $id));
        }

        return str_replace('--', ':', $id);
    }

    /**
     * Transforms a valid icon name into an ID.
     *
     * @throws \InvalidArgumentException if the name is not valid
     * @see isValidName()
     */
    public static function nameToId(string $name): string
    {
        if (!self::isValidName($name)) {
            throw new \InvalidArgumentException(sprintf('The name "%s" is not a valid name.', $name));
        }

        return str_replace(':', '--', $name);
    }

    /**
     * Returns whether the given string is a valid icon ID.
     *
     * An icon ID is a string that contains only lowercase letters, numbers, and hyphens.
     * It must be composed of slugs separated by double hyphens.
     *
     * @see https://regex101.com/r/mmvl5t/1
     */
    public static function isValidId(string $id): bool
    {
        return (bool) preg_match('#^([a-z0-9]+(-[a-z0-9]+)*)(--[a-z0-9]+(-[a-z0-9]+)*)*$#', $id);
    }

    /**
     * Returns whether the given string is a valid icon name.
     *
     * An icon name is a string that contains only lowercase letters, numbers, and hyphens.
     * It must be composed of slugs separated by colons.
     *
     * @see https://regex101.com/r/Gh2Z9s/1
     */
    public static function isValidName(string $name): bool
    {
        return (bool) preg_match('#^([a-z0-9]+(-[a-z0-9]+)*)(:[a-z0-9]+(-[a-z0-9]+)*)*$#', $name);
    }

    public function __construct(
        private readonly string $innerSvg,
        private readonly array $attributes = [],
    )
    {
        // @todo validate attributes (?)
        // the main idea is to have a way to validate the attributes
        // before the icon is cached to improve performances
        // (avoiding to validate the attributes each time the icon is rendered)
    }

    public function toHtml(): string
    {
        $htmlAttributes = '';
        foreach ($this->attributes as $name => $value) {
            if (false === $value) {
                continue;
            }
            $htmlAttributes .= ' '.$name;
            if (true !== $value) {
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $htmlAttributes .= '="'. $value .'"';
            }
        }

        return '<svg'.$htmlAttributes.'>'.$this->innerSvg.'</svg>';
    }

    public function getInnerSvg(): string
    {
        return $this->innerSvg;
    }

    /**
     * @return array<string, string|bool>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array<string, string|bool> $attributes
     * @return self
     */
    public function withAttributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException(sprintf('Attribute names must be string, "%s" given.', get_debug_type($name)));
            }
            // @todo regexp would be better ?
            if (!ctype_alnum($name) && !str_contains($name, '-')) {
                throw new \InvalidArgumentException(sprintf('Invalid attribute name "%s".', $name));
            }
            if (!is_string($value) && !is_bool($value)) {
                throw new \InvalidArgumentException(sprintf('Invalid value type for attribute "%s". Boolean or string allowed, "%s" provided. ', $name, get_debug_type($value)));
            }
        }

        return new self($this->innerSvg, [...$this->attributes, ...$attributes]);
    }

    public function withInnerSvg(string $innerSvg): self
    {
        // @todo validate svg ?
        // The main idea is to not validate the attributes for every icon
        // when they come from a pack (and thus share a set of attributes)

        return new self($innerSvg, $this->attributes);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    public function serialize(): string
    {
        return serialize([$this->innerSvg, $this->attributes]);
    }

    public function unserialize(string $data): void
    {
        [$this->innerSvg, $this->attributes] = unserialize($data);
    }

    public function __serialize(): array
    {
        return [$this->innerSvg, $this->attributes];
    }

    public function __unserialize(array $data): void
    {
        [$this->innerSvg, $this->attributes] = $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('The Icon object is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('The Icon object is immutable.');
    }
}
