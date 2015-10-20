<?php

class Test
{
    public function isTest()
    {
        //testSkipFunctionCallStart
        $this->function1(
            'abc' , $this->function1( $this, 'final' ))->function1('a', 'b');
        //testSkipFunctionCallNoEnd
        $this->function1('abc', $this->function1();

        //testSkipBlock
        if (true ) {
            if (true) {
                echo "hello";
            }
        }

        //testGetNextIndexOfSimpleToken
        $this->function1('a' , 'b') ;
        $this->function1('a' , 'b') ;

        //testGetNextIndexOfTokenType
        $this->function1('a', 'b');
        return true;

        //testGetPrevIndexOfTokenType
        //testGetPrevIndexOfTokenTypeNotFound
    }

    //testGetFunctionArguments
    public function function1( $input1,
                               $input2 = 'abc',
                               array $input3 = ['a', 'b'], MyType $input4= null)
    {
        return null;
    }

    //testGetFunctionArgumentsEmpty
    public function function2( )
    {
        return 0;
    }

    //testGetFunctionStartingIndex

    /**
     * @return int
     */
    public function function3()
    {
        return 1;
    }

    //testGetFunctionStartingIndexNoDocComment

    public function function4()
    {
        //testGetCallArguments
        $this->function1($this, 'abc', array ( 'a','b' ), null );

        //testGetCallArgumentsEmpty
        $this->function3( );

        //testGetCallArgumentsOneArgument
        $this->function1( 'abc' );

        //testReplaceCallArgumentsTokens
        $this->function1($this, 'abc', array ( 'a','b' ), null );

        //testReplaceCallArgumentsTokensNoArgument
        $this->function3();
    }

    //testGetNextLineIndex
    //testGetPrevLineIndex

}

//testSkipBlockMismatched should not have '}' after this
if (true) {

        //}
    //testGetNextIndexOfSimpleTokenNotFound, should not have ')' after this
    $this->function1('a' , 'b' ;
