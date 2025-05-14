<?php declare(strict_types=1);

namespace DevTheorem\Handlebars;

use DevTheorem\Handlebars\Ast\ArrayLiteral;
use DevTheorem\Handlebars\Ast\BooleanLiteral;
use DevTheorem\Handlebars\Ast\CloseBlock;
use DevTheorem\Handlebars\Ast\CommentStatement;
use DevTheorem\Handlebars\Ast\ContentStatement;
use DevTheorem\Handlebars\Ast\Hash;
use DevTheorem\Handlebars\Ast\HashLiteral;
use DevTheorem\Handlebars\Ast\HashPair;
use DevTheorem\Handlebars\Ast\InverseChain;
use DevTheorem\Handlebars\Ast\NullLiteral;
use DevTheorem\Handlebars\Ast\NumberLiteral;
use DevTheorem\Handlebars\Ast\OpenBlock;
use DevTheorem\Handlebars\Ast\OpenHelper;
use DevTheorem\Handlebars\Ast\OpenPartialBlock;
use DevTheorem\Handlebars\Ast\PartialStatement;
use DevTheorem\Handlebars\Ast\PathSegment;
use DevTheorem\Handlebars\Ast\StringLiteral;
use DevTheorem\Handlebars\Ast\SubExpression;
use DevTheorem\Handlebars\Ast\UndefinedLiteral;

/* This is an automatically GENERATED file, which should not be manually edited.
 * Instead edit one of the following:
 *  * the grammar file grammar/handlebars.y
 *  * the skeleton file grammar/parser.template
 *  * the preprocessing script grammar/rebuildParser.php
 */
class Parser extends ParserAbstract
{
    public const YYERRTOK = 256;
    public const BOOLEAN = 257;
    public const CLOSE = 258;
    public const CLOSE_ARRAY = 259;
    public const CLOSE_BLOCK_PARAMS = 260;
    public const CLOSE_RAW_BLOCK = 261;
    public const CLOSE_SEXPR = 262;
    public const CLOSE_UNESCAPED = 263;
    public const COMMENT = 264;
    public const CONTENT = 265;
    public const DATA = 266;
    public const END_RAW_BLOCK = 267;
    public const EQUALS = 268;
    public const ID = 269;
    public const INVALID = 270;
    public const INVERSE = 271;
    public const NULL = 272;
    public const NUMBER = 273;
    public const OPEN = 274;
    public const OPEN_ARRAY = 275;
    public const OPEN_BLOCK = 276;
    public const OPEN_BLOCK_PARAMS = 277;
    public const OPEN_ENDBLOCK = 278;
    public const OPEN_INVERSE = 279;
    public const OPEN_INVERSE_CHAIN = 280;
    public const OPEN_PARTIAL = 281;
    public const OPEN_PARTIAL_BLOCK = 282;
    public const OPEN_RAW_BLOCK = 283;
    public const OPEN_SEXPR = 284;
    public const OPEN_UNESCAPED = 285;
    public const PRIVATE_SEP = 286;
    public const SEP = 287;
    public const STRING = 288;
    public const UNDEFINED = 289;

    protected int $tokenToSymbolMapSize = 290;
    protected int $actionTableSize = 52;
    protected int $gotoTableSize = 53;

    protected int $invalidSymbol = 35;
    protected int $errorSymbol = 1;
    protected int $defaultAction = -32766;
    protected int $unexpectedTokenRule = 32767;

    protected int $YY2TBLSTATE = 29;
    protected int $numNonLeafStates = 75;

    protected array $symbolToName = array(
        "EOF",
        "error",
        "BOOLEAN",
        "CLOSE",
        "CLOSE_ARRAY",
        "CLOSE_BLOCK_PARAMS",
        "CLOSE_RAW_BLOCK",
        "CLOSE_SEXPR",
        "CLOSE_UNESCAPED",
        "COMMENT",
        "CONTENT",
        "DATA",
        "END_RAW_BLOCK",
        "EQUALS",
        "ID",
        "INVERSE",
        "NULL",
        "NUMBER",
        "OPEN",
        "OPEN_ARRAY",
        "OPEN_BLOCK",
        "OPEN_BLOCK_PARAMS",
        "OPEN_ENDBLOCK",
        "OPEN_INVERSE",
        "OPEN_INVERSE_CHAIN",
        "OPEN_PARTIAL",
        "OPEN_PARTIAL_BLOCK",
        "OPEN_RAW_BLOCK",
        "OPEN_SEXPR",
        "OPEN_UNESCAPED",
        "PRIVATE_SEP",
        "SEP",
        "STRING",
        "UNDEFINED",
        "INVALID"
    );

    protected array $tokenToSymbol = array(
            0,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   35,   35,   35,   35,   35,    1,    2,    3,    4,
            5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
           34,   15,   16,   17,   18,   19,   20,   21,   22,   23,
           24,   25,   26,   27,   28,   29,   30,   31,   32,   33
    );

    protected array $action = array(
          134,  129,   85,  139,  138,    0,  104,  105,   90,   36,
          128,   12,  143,   18,  136,  133,   19,   65,   14,   15,
           20,  107,   16,  109,  103,   93,   13,   94,   95,   86,
          132,  135,  124,  116,  117,   89,   42,  106,   22,    0,
            0,   21,    0,   17,    0,   47,  143,  142,  127,   44,
            0,   62
    );

    protected array $actionCheck = array(
            2,    5,    9,   30,   31,    0,    3,    3,    6,   11,
           14,   18,   14,   20,   16,   17,   23,   19,   25,   26,
           27,    3,   29,    3,    3,    3,   28,    3,    3,   10,
           32,   33,    4,    7,    7,   12,   21,    8,   22,   -1,
           -1,   24,   -1,   13,   -1,   14,   14,   14,   14,   14,
           -1,   15
    );

    protected array $actionBase = array(
            0,   28,   35,   35,   35,   35,   35,   35,   35,   35,
           35,   -7,   35,   35,   -2,   -2,   -2,   -2,   -2,   -2,
           -2,   -2,   -2,   17,   17,  -27,  -27,  -27,   23,  -27,
          -27,   -4,   36,   15,   15,   15,   32,   31,   16,   32,
           16,   16,   34,    5,   30,    3,   26,   30,   33,    4,
           18,   20,    2,   29,   21,   27,   22,   24,   25,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,   -2,   -2,   -2,   -2,
           -2,   -2,   -2,   -2,   -2,   -2,   19,   -2,   -2,    0,
            0,    0,    0,    0,    0,    0,    0,    0,   36,   36,
            0,    0,    0,   19
    );

    protected array $actionDefault = array(
            3,32767,   45,   45,   45,   45,   45,   45,   45,   45,
           45,    1,32767,32767,32767,32767,32767,32767,32767,32767,
        32767,32767,32767,   25,   25,   38,   66,32767,32767,   62,
           65,32767,   22,   51,   51,   51,32767,   43,32767,32767,
        32767,32767,32767,32767,   68,32767,32767,32767,32767,32767,
        32767,32767,32767,32767,32767,32767,32767,32767,32767,    3,
            3,    3,    3,    3,   13,   36,   36,   36,   36,   36,
           36,   36,   36,   36,   36
    );

    protected array $goto = array(
            2,    3,    4,    5,    6,    7,    8,    9,   10,   33,
           34,   50,   51,   52,   53,   55,   35,   66,   73,   69,
           70,   72,  123,   45,   46,   67,   68,   71,   74,   54,
           27,   27,   27,   27,   27,   23,   32,   38,   98,   24,
           29,    0,   39,   30,   39,  101,   87,   57,   58,   91,
           92,   96,  122
    );

    protected array $gotoCheck = array(
           13,   13,   13,   13,   13,   13,   13,   13,   13,   14,
           14,   14,   14,   14,   14,   14,   14,   25,   25,   25,
           25,   25,   25,   24,   24,   12,   12,   12,   12,   12,
           27,   27,   27,   27,   27,    1,    1,    1,    1,    1,
           36,   -1,   37,   36,   37,   16,    9,   20,   20,   17,
           17,   22,   31
    );

    protected array $gotoBase = array(
            0,  -24,    0,    0,    0,    0,    0,    0,    0,   18,
            0,    0,    7,  -66,    6,    0,   21,    9,    0,    0,
           13,    0,   19,    0,   11,    5,    0,   12,    0,    0,
            0,   15,    0,    0,    0,    0,    4,   17
    );

    protected array $gotoDefault = array(
        -32768,   43,   11,   77,   79,   80,   81,   82,   83,   84,
           28,   64,  112,    1,   49,   59,   40,  108,   60,   41,
           56,   63,  102,   99,  119,  110,   61,   25,  114,  115,
           37,  121,  125,   31,  130,  131,   26,   48
    );

    protected array $ruleToNonTerminal = array(
            0,    1,    2,    2,    3,    3,    3,    3,    3,    3,
            3,    9,   10,   10,    6,   11,    5,    5,   15,   18,
           21,   19,   19,   22,   16,   16,   23,   23,   17,    4,
            4,    4,    7,    8,   26,   13,   13,   25,   25,   27,
           27,   29,   29,   24,   14,   14,   30,   30,   31,   28,
           20,   20,   33,   33,   32,   12,   12,   12,   12,   12,
           12,   12,   35,   37,   37,   34,   34,   36,   36
    );

    protected array $ruleToLength = array(
            1,    1,    2,    0,    1,    1,    1,    1,    1,    1,
            1,    1,    2,    0,    3,    5,    4,    4,    6,    6,
            6,    1,    0,    2,    1,    0,    3,    1,    3,    3,
            5,    5,    5,    3,    5,    2,    0,    1,    1,    1,
            1,    3,    5,    1,    1,    0,    1,    2,    3,    3,
            1,    0,    1,    2,    3,    1,    1,    1,    1,    1,
            1,    1,    2,    1,    1,    3,    1,    3,    1
    );

    protected function initReduceCallbacks(): void {
        $this->reduceCallbacks = [
            0 => null,
            1 => static function ($self, $stackPos) {
                 $self->semValue = $self->prepareProgram($self->semStack[$stackPos-(1-1)]); 
            },
            2 => static function ($self, $stackPos) {
                 if ($self->semStack[$stackPos-(2-2)] !== null) { $self->semStack[$stackPos-(2-1)][] = $self->semStack[$stackPos-(2-2)]; } $self->semValue = $self->semStack[$stackPos-(2-1)]; 
            },
            3 => static function ($self, $stackPos) {
                 $self->semValue = []; 
            },
            4 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            5 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            6 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            7 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            8 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            9 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            10 => static function ($self, $stackPos) {
                
        $self->semValue = new CommentStatement(
            value: $self->stripComment($self->semStack[$stackPos-(1-1)]),
            strip: $self->stripFlags($self->semStack[$stackPos-(1-1)], $self->semStack[$stackPos-(1-1)]),
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos]),
        );
  
            },
            11 => static function ($self, $stackPos) {
                
        $self->semValue = new ContentStatement(
            value: $self->semStack[$stackPos-(1-1)],
            original: $self->semStack[$stackPos-(1-1)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            12 => static function ($self, $stackPos) {
                 if ($self->semStack[$stackPos-(2-2)] !== null) { $self->semStack[$stackPos-(2-1)][] = $self->semStack[$stackPos-(2-2)]; } $self->semValue = $self->semStack[$stackPos-(2-1)]; 
            },
            13 => static function ($self, $stackPos) {
                 $self->semValue = []; 
            },
            14 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareRawBlock($self->semStack[$stackPos-(3-1)], $self->semStack[$stackPos-(3-2)], $self->semStack[$stackPos-(3-3)], $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]));
    
            },
            15 => static function ($self, $stackPos) {
                
        $self->semValue = new OpenHelper($self->semStack[$stackPos-(5-2)], $self->semStack[$stackPos-(5-3)], $self->semStack[$stackPos-(5-4)]);
    
            },
            16 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareBlock($self->semStack[$stackPos-(4-1)], $self->semStack[$stackPos-(4-2)], $self->semStack[$stackPos-(4-3)], $self->semStack[$stackPos-(4-4)], false, $self->locInfo($self->tokenStartStack[$stackPos-(4-1)], $self->tokenEndStack[$stackPos]));
    
            },
            17 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareBlock($self->semStack[$stackPos-(4-1)], $self->semStack[$stackPos-(4-2)], $self->semStack[$stackPos-(4-3)], $self->semStack[$stackPos-(4-4)], true, $self->locInfo($self->tokenStartStack[$stackPos-(4-1)], $self->tokenEndStack[$stackPos]));
    
            },
            18 => static function ($self, $stackPos) {
                
        $self->semValue = new OpenBlock(
            open: $self->semStack[$stackPos-(6-1)],
            path: $self->semStack[$stackPos-(6-2)],
            params: $self->semStack[$stackPos-(6-3)],
            hash: $self->semStack[$stackPos-(6-4)],
            blockParams: $self->semStack[$stackPos-(6-5)],
            strip: $self->stripFlags($self->semStack[$stackPos-(6-1)], $self->semStack[$stackPos-(6-6)]),
        );
    
            },
            19 => static function ($self, $stackPos) {
                
        $self->semValue = new OpenBlock(
            open: $self->semStack[$stackPos-(6-1)],
            path: $self->semStack[$stackPos-(6-2)],
            params: $self->semStack[$stackPos-(6-3)],
            hash: $self->semStack[$stackPos-(6-4)],
            blockParams: $self->semStack[$stackPos-(6-5)],
            strip: $self->stripFlags($self->semStack[$stackPos-(6-1)], $self->semStack[$stackPos-(6-6)]),
        );
    
            },
            20 => static function ($self, $stackPos) {
                
        $self->semValue = new OpenBlock(
            open: $self->semStack[$stackPos-(6-1)],
            path: $self->semStack[$stackPos-(6-2)],
            params: $self->semStack[$stackPos-(6-3)],
            hash: $self->semStack[$stackPos-(6-4)],
            blockParams: $self->semStack[$stackPos-(6-5)],
            strip: $self->stripFlags($self->semStack[$stackPos-(6-1)], $self->semStack[$stackPos-(6-6)]),
        );
    
            },
            21 => null,
            22 => static function ($self, $stackPos) {
                 $self->semValue = null; 
            },
            23 => static function ($self, $stackPos) {
                
        $self->semValue = new InverseChain(
            strip: $self->stripFlags($self->semStack[$stackPos-(2-1)], $self->semStack[$stackPos-(2-1)]),
            program: $self->semStack[$stackPos-(2-2)],
        );
    
            },
            24 => null,
            25 => static function ($self, $stackPos) {
                 $self->semValue = null; 
            },
            26 => static function ($self, $stackPos) {
                
        $inverse = $self->prepareBlock($self->semStack[$stackPos-(3-1)], $self->semStack[$stackPos-(3-2)], $self->semStack[$stackPos-(3-3)], $self->semStack[$stackPos-(3-3)], false, $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]));
        $program = $self->prepareProgram([$inverse], $self->semStack[$stackPos-(3-2)]->loc);
        $program->chained = true;

        $self->semValue = new InverseChain($self->semStack[$stackPos-(3-1)]->strip, $program, true);
  
            },
            27 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            28 => static function ($self, $stackPos) {
                
        $self->semValue = new CloseBlock($self->semStack[$stackPos-(3-2)], $self->stripFlags($self->semStack[$stackPos-(3-1)], $self->semStack[$stackPos-(3-3)]));
    
            },
            29 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareMustache(
            path: new HashLiteral($self->semStack[$stackPos-(3-2)]->pairs, $self->semStack[$stackPos-(3-2)]->loc),
            params: [],
            hash: null,
            open: $self->semStack[$stackPos-(3-1)],
            strip: $self->stripFlags($self->semStack[$stackPos-(3-1)], $self->semStack[$stackPos-(3-3)]),
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            30 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareMustache(
            path: $self->semStack[$stackPos-(5-2)],
            params: $self->semStack[$stackPos-(5-3)],
            hash: $self->semStack[$stackPos-(5-4)],
            open: $self->semStack[$stackPos-(5-1)],
            strip: $self->stripFlags($self->semStack[$stackPos-(5-1)], $self->semStack[$stackPos-(5-5)]),
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(5-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            31 => static function ($self, $stackPos) {
                
        $self->semValue = $self->prepareMustache(
            path: $self->semStack[$stackPos-(5-2)],
            params: $self->semStack[$stackPos-(5-3)],
            hash: $self->semStack[$stackPos-(5-4)],
            open: $self->semStack[$stackPos-(5-1)],
            strip: $self->stripFlags($self->semStack[$stackPos-(5-1)], $self->semStack[$stackPos-(5-5)]),
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(5-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            32 => static function ($self, $stackPos) {
                
        $self->semValue = new PartialStatement(
            name: $self->semStack[$stackPos-(5-2)],
            params: $self->semStack[$stackPos-(5-3)],
            hash: $self->semStack[$stackPos-(5-4)],
            indent: '',
            strip: $self->stripFlags($self->semStack[$stackPos-(5-1)], $self->semStack[$stackPos-(5-5)]),
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(5-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            33 => static function ($self, $stackPos) {
                
        $self->semValue = $self->preparePartialBlock(
            open: $self->semStack[$stackPos-(3-1)],
            program: $self->semStack[$stackPos-(3-2)],
            close: $self->semStack[$stackPos-(3-3)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            34 => static function ($self, $stackPos) {
                
        $self->semValue = new OpenPartialBlock(
            path: $self->semStack[$stackPos-(5-2)],
            params: $self->semStack[$stackPos-(5-3)],
            hash: $self->semStack[$stackPos-(5-4)],
            strip: $self->stripFlags($self->semStack[$stackPos-(5-1)], $self->semStack[$stackPos-(5-5)]),
        );
    
            },
            35 => static function ($self, $stackPos) {
                 if ($self->semStack[$stackPos-(2-2)] !== null) { $self->semStack[$stackPos-(2-1)][] = $self->semStack[$stackPos-(2-2)]; } $self->semValue = $self->semStack[$stackPos-(2-1)]; 
            },
            36 => static function ($self, $stackPos) {
                 $self->semValue = []; 
            },
            37 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            38 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            39 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            40 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            41 => static function ($self, $stackPos) {
                 $self->semValue = new HashLiteral($self->semStack[$stackPos-(3-2)]->pairs, $self->semStack[$stackPos-(3-2)]->loc); 
            },
            42 => static function ($self, $stackPos) {
                
        $self->semValue = new SubExpression(
            path: $self->semStack[$stackPos-(5-2)],
            params: $self->semStack[$stackPos-(5-3)],
            hash: $self->semStack[$stackPos-(5-4)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(5-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            43 => static function ($self, $stackPos) {
                
        $self->semValue = new Hash($self->semStack[$stackPos-(1-1)], $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos]));
    
            },
            44 => null,
            45 => static function ($self, $stackPos) {
                 $self->semValue = null; 
            },
            46 => static function ($self, $stackPos) {
                 $self->semValue = [$self->semStack[$stackPos-(1-1)]]; 
            },
            47 => static function ($self, $stackPos) {
                 if ($self->semStack[$stackPos-(2-2)] !== null) { $self->semStack[$stackPos-(2-1)][] = $self->semStack[$stackPos-(2-2)]; } $self->semValue = $self->semStack[$stackPos-(2-1)]; 
            },
            48 => static function ($self, $stackPos) {
                
        $self->semValue = new HashPair(
            key: $self->id($self->semStack[$stackPos-(3-1)]),
            value: $self->semStack[$stackPos-(3-3)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            49 => static function ($self, $stackPos) {
                
        $self->semValue = new ArrayLiteral($self->semStack[$stackPos-(3-2)], $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]));
    
            },
            50 => null,
            51 => static function ($self, $stackPos) {
                 $self->semValue = []; 
            },
            52 => static function ($self, $stackPos) {
                 $self->semValue = [$self->semStack[$stackPos-(1-1)]]; 
            },
            53 => static function ($self, $stackPos) {
                 if ($self->semStack[$stackPos-(2-2)] !== null) { $self->semStack[$stackPos-(2-1)][] = $self->semStack[$stackPos-(2-2)]; } $self->semValue = $self->semStack[$stackPos-(2-1)]; 
            },
            54 => static function ($self, $stackPos) {
                
        $self->semValue = array_map($self->id(...), $self->semStack[$stackPos-(3-2)]);
    
            },
            55 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            56 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            57 => static function ($self, $stackPos) {
                 $self->semValue = new StringLiteral($self->semStack[$stackPos-(1-1)], $self->semStack[$stackPos-(1-1)], $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos])); 
            },
            58 => static function ($self, $stackPos) {
                 $self->semValue = new NumberLiteral($self->semStack[$stackPos-(1-1)] + 0, $self->semStack[$stackPos-(1-1)] + 0, $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos])); 
            },
            59 => static function ($self, $stackPos) {
                 $self->semValue = new BooleanLiteral($self->semStack[$stackPos-(1-1)] === 'true', $self->semStack[$stackPos-(1-1)] === 'true', $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos])); 
            },
            60 => static function ($self, $stackPos) {
                 $self->semValue = new UndefinedLiteral($self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos])); 
            },
            61 => static function ($self, $stackPos) {
                 $self->semValue = new NullLiteral($self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos])); 
            },
            62 => static function ($self, $stackPos) {
                
        $self->semValue = $self->preparePath(
            data: true,
            sexpr: null,
            parts: $self->semStack[$stackPos-(2-2)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(2-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            63 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            64 => static function ($self, $stackPos) {
                 $self->semValue = $self->semStack[$stackPos-(1-1)]; 
            },
            65 => static function ($self, $stackPos) {
                
        $self->semValue = $self->preparePath(
            data: false,
            sexpr: $self->semStack[$stackPos-(3-1)],
            parts: $self->semStack[$stackPos-(3-3)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(3-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            66 => static function ($self, $stackPos) {
                
        $self->semValue = $self->preparePath(
            data: false,
            sexpr: null,
            parts: $self->semStack[$stackPos-(1-1)],
            loc: $self->locInfo($self->tokenStartStack[$stackPos-(1-1)], $self->tokenEndStack[$stackPos]),
        );
    
            },
            67 => static function ($self, $stackPos) {
                
        $self->semStack[$stackPos-(3-1)][] = new PathSegment($self->id($self->semStack[$stackPos-(3-3)]), $self->semStack[$stackPos-(3-3)], $self->semStack[$stackPos-(3-2)]); $self->semValue = $self->semStack[$stackPos-(3-1)];
    
            },
            68 => static function ($self, $stackPos) {
                
        $self->semValue = [new PathSegment($self->id($self->semStack[$stackPos-(1-1)]), $self->semStack[$stackPos-(1-1)], null)];
    
            },
        ];
    }
}
