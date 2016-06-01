#include "Parser.h"
#include<stack>

#pragma once
class Differentiate : public Parser<Expression>
{
public:
	explicit Differentiate(const char* _input, std::vector<std::string> &vars): Parser(_input, vars){}
	explicit Differentiate(): Parser(){}

	virtual ~Differentiate();
	virtual Expression eval(const Expression&);

private:
	bool isContainVar(const Expression&)const;
	bool isContainConst(const Expression&)const;
	bool Differentiate::isContainVars(const Expression&);

};