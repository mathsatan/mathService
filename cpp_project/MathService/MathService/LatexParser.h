#include "Parser.h"

#pragma once

class LatexParser: public Parser<std::string> {

public:
	explicit LatexParser(const char* _input, std::vector<std::string> &vars): Parser(_input, vars){}
	explicit LatexParser(): Parser(){}

	virtual ~LatexParser();

	virtual std::string eval(const Expression& e);
};