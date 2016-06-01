#include "LatexParser.h"

std::string LatexParser::eval(const Expression& e) {
	switch (e.getArgs().size()) {
	case 2: {
		auto a = eval((e.getArgs())[0]);
		auto b = eval((e.getArgs())[1]);
		
		if (!e.getToken().compare("/")) return ("\\dfrac{" + a + "}{" + b + '}'); 
		if (!e.getToken().compare("**")) return (a + "^{" + b + '}'); 
		if (!e.getToken().compare("*")) return (a + "\\cdot " + b); 
		return ('(' + a + e.getToken() + b + ')');
		
		throw std::runtime_error("LatexParser: Unknown binary operator" + e.getToken());
	}
	case 1: {
		auto a = eval((e.getArgs())[0]);
		if (!e.getToken().compare("+")) return a;
		if (!e.getToken().compare("-")) return ("(-" + a + ')');
		if (!e.getToken().compare("abs")) return ('|' + a + '|');
		if (!e.getToken().compare("sin")) return ("\\sin(" + a + ')');
		if (!e.getToken().compare("cos")) return ("\\cos(" + a + ')');	

		if (!e.getToken().compare("ln")) return "\\ln(" + a + ')';	
		if (!e.getToken().compare("tg")) return "\\tan(" + a + ')';
		if (!e.getToken().compare("ctg")) return "\\cot(" + a + ')';
		if (!e.getToken().compare("sqrt")) return "\\sqrt{" + a + '}';

		if (!e.getToken().compare("arcsin")) return "\\arcsin(" + a + ')';
		if (!e.getToken().compare("arccos")) return "\\arccos(" + a + ')';
		if (!e.getToken().compare("arctg")) return "\\arctan(" + a + ')';
		if (!e.getToken().compare("arcctg")) return "\\textrm{arccot}(" + a + ')';

		if(this->isContainsVariable(e.getToken().c_str())){
			return e.getToken();
		}
		throw std::runtime_error("LatexParser: Unknown unary operator" + e.getToken());
	}
	case 0:
		return e.getToken();
	}
	throw std::runtime_error("LatexParser: Unknown expression type");
}

// virtual destructor
LatexParser::~LatexParser(){
}