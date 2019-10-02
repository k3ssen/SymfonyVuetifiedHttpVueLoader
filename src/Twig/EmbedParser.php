<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Token;
use Twig\TokenParser\EmbedTokenParser;
use Twig\TokenParser\IncludeTokenParser;

/**
 * Replaces Twig_TokenParser_Embed to allow using content in embed that has no block.
 * eg: {% embemd 'somefile.twig %}Content{% endembed %}
 * would be the same as: {% embemd 'somefile.twig %}{% block body %}Content{% endblock %}{% endembed %}
 */
class EmbedParser extends IncludeTokenParser
{
    protected CONST TAG_NAME = 'embed';
    protected CONST WRAP_BLOCK_NAME = 'body';

    public function parse(Token $token)
    {
        // Fetch the 'current' of the stream before doing any manipulations
        $currentNr = $this->getStreamCurrentNr();
        // Wrap block (if needed)
        $this->wrapBlockIfMissing();
        // After injections, the current will be affected, so set the current back to what it was before
        $this->setStreamCurrentNr($currentNr);

        // Now let the stand EmbedTokenParser handle everything else.
        $embedParser = new EmbedTokenParser();
        $embedParser->setParser($this->parser);
        return $embedParser->parse($token);
    }

    public function getTag(): string
    {
        return static::TAG_NAME;
    }

    /**
     * Find content that isn't wrapped inside a block. If such content is find, then wrap that content using a block
     * with the WRAP_BLOCK_NAME.
     */
    protected function wrapBlockIfMissing(): void
    {
        $stream = $this->parser->getStream();

        // Move the stream 'current' to the beginning of the inside of the embed
        do {
            $stream->next();
        } while (!$stream->getCurrent()->test(\Twig_Token::BLOCK_END_TYPE));

        if ($this->injectWrapStart()){
            $this->injectWrapEnd();
        }
    }

    protected function injectWrapStart(): bool
    {
        $stream = $this->parser->getStream();
        $subBlock = 0;
        do {
            if ($subBlock === 0 && $this->shouldWrap($stream)) {
                $line = $stream->look()->getLine();
                $stream->injectTokens([
                    new \Twig_Token(\Twig_Token::BLOCK_START_TYPE, '', $line),
                    new \Twig_Token(\Twig_Token::NAME_TYPE, 'block', $line),
                    new \Twig_Token(\Twig_Token::NAME_TYPE, static::WRAP_BLOCK_NAME, $line),
                    new \Twig_Token(\Twig_Token::BLOCK_END_TYPE, '', $line),
                ]);
                return true;
            } elseif ($this->nextIsBlock($stream)) {
                $subBlock++;
            } elseif($this->nextIsEndBlock($stream)) {
                $subBlock--;
                //Additional next so that wrapping won't be applied before this endblock.
                $stream->next();
            } elseif ($stream->look(2)->test(\Twig_Token::NAME_TYPE, 'endembed')) {
                //If endembed was found before any wrapping was needed, then no wrapping is needed at all
                return false;
            }
        } while ($stream->next());
        return false;
    }

    protected function injectWrapEnd(): bool
    {
        $stream = $this->parser->getStream();
        $subEmbed = 0;
        do {
            if ($stream->look(2)->test(\Twig_Token::NAME_TYPE, 'embed')) {
                $subEmbed++;
            }
            if ($stream->look(2)->test(\Twig_Token::NAME_TYPE, 'endembed')) {
                $subEmbed--;
            }
            if ($subEmbed < 0 || ($subEmbed === 0 && $this->nextIsBlock($stream))) {
                $stream->next();
                $line = $stream->look()->getLine();
                $stream->injectTokens([
                    new \Twig_Token(\Twig_Token::BLOCK_START_TYPE, '', $line),
                    new \Twig_Token(\Twig_Token::NAME_TYPE, 'endblock', $line),
                    new \Twig_Token(\Twig_Token::BLOCK_END_TYPE, '', $line),
                ]);
                return true;
            }
        } while ($stream->next());
        return false;
    }

    protected function nextIsBlock(\Twig_TokenStream $stream): bool
    {
        return $stream->look()->test(\Twig_Token::BLOCK_START_TYPE)
            && $stream->look(2)->test(\Twig_Token::NAME_TYPE, 'block');
    }
    protected function nextIsEndBlock(\Twig_TokenStream $stream): bool
    {
        return $stream->look()->test(\Twig_Token::BLOCK_START_TYPE)
            && $stream->look(2)->test(\Twig_Token::NAME_TYPE, 'endblock');
    }

    protected function shouldWrap(\Twig_TokenStream $stream): bool
    {
        $token = $stream->getCurrent();
        $textWithContent = $token->test(\Twig_Token::TEXT_TYPE) && trim($token->getValue());
        $blockTokenWithoutBlockName = $token->test(\Twig_Token::BLOCK_START_TYPE)
            && $stream->look()->test(\Twig_Token::NAME_TYPE, 'block') === false
        ;
        $varToken = $token->test(\Twig_Token::VAR_START_TYPE);
        return $textWithContent || $blockTokenWithoutBlockName || $varToken;
    }

    protected function getStreamCurrentNr(): int
    {
        return $this->getStreamCurrentReflectionProperty()->getValue($this->parser->getStream());
    }

    protected function setStreamCurrentNr(int $nr): void
    {
        $this->getStreamCurrentReflectionProperty()->setValue($this->parser->getStream(), $nr);
    }

    /**
     * The 'current' property in \Twig_TokenStream is private, so ReflectionClass is used to access this token.
     */
    protected function getStreamCurrentReflectionProperty(): \ReflectionProperty
    {
        $property = (new \ReflectionClass($this->parser->getStream()))->getProperty("current");
        $property->setAccessible(true);
        return $property;
    }
}
