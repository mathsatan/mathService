#include "Integrate.h"

Integrate::Integrate(const std::map<std::string, double> &vars, const double _a, const double _b) : Calculate<double>(){	
	this->a = _a;
	this->b = _b;
	this->setVariables(vars);
}

Integrate::Integrate() : Calculate<double>(){
	this->a = 0.0f;
	this->b = 1.0f;
	this->variables.insert(std::pair<std::string, double>("x", 0.0f));	
}

Integrate::~Integrate(){
}

double Integrate::calc(const Expression& e){	
	double result = 0.0f;
	const int N = 1000;
	double tau = (b - a) / N;
	try{
		for(double i = this->a; i < this->b; i += tau){
			this->setVariable("x", i);
			result += this->evaluate(e);
		}
	}catch(std::exception& e){
		throw e;
	}	
	
	return result * tau;
}

double Integrate::evaluate(const Expression& e) {
	switch (e.getArgs().size()) {
	case 2: {
		auto a = evaluate((e.getArgs())[0]);
		auto b = evaluate((e.getArgs())[1]);
		if (!e.getToken().compare("+")) return a + b;
		if (!e.getToken().compare("-")) return a - b;
		if (!e.getToken().compare("*")) return a * b;
		if (!e.getToken().compare("/")) return a / b;
		if (!e.getToken().compare("**")) return pow(a, b);
		if (!e.getToken().compare("mod")) return (int)a % (int)b;
	
		throw std::runtime_error("Unknown binary operator");
	}
	case 1: {
		auto a = evaluate((e.getArgs())[0]);
		if (!e.getToken().compare("+")) return +a;
		if (!e.getToken().compare("-")) return -a;
		if (!e.getToken().compare("abs")) return abs(a);
		if (!e.getToken().compare("sin")) return sin(a);
		if (!e.getToken().compare("cos")) return cos(a);	
		if (!e.getToken().compare("ln")) return log(a);	
		if (!e.getToken().compare("tg")) return tan(a);
		if (!e.getToken().compare("ctg")) return 1.0f/tan(a);

		if (!e.getToken().compare("arcsin")) return asin(a);	
		if (!e.getToken().compare("arccos")) return acos(a);	
		if (!e.getToken().compare("arctg")) return atan(a);
		if (!e.getToken().compare("arcctg")) return 1.0f/atan(a);

		throw std::runtime_error("Unknown unary operator");
	}
	case 0:
		if (this->isNumber(e.getToken())){
			return strtod(e.getToken().c_str(), nullptr);
		}else{
			auto it = this->variables.find(e.getToken());
			if (it != this->variables.end()) {
				return it->second;		
			}
		}
	}
	throw std::runtime_error("Unknown expression type or variable name");
}

bool Integrate::isNumber(const std::string& s){
	std::cmatch cm;
	if (std::regex_match (s.c_str(), cm, std::regex("^[0-9]+(\\.[0-9]+)?$"))){
		return 1;
	}
	return 0;
}