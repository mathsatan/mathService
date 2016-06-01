#include <cmath>
#include <stdexcept>
#include <iostream>
#include <vector>
#include <regex>

#include "Expression.h"

#pragma once

// Abstract class
template<class T> class Parser{
public:
	explicit Parser(const char* _input, std::vector<std::string> &vars);
	explicit Parser();

	virtual ~Parser();		// virtual destructor
	virtual Expression parse(const char* = 0);
			
	virtual T eval(const Expression& e) = 0;	// virtual pure evaluate function

	bool isContainsVariable(const char* variableName) const;
	void addVariable(const char* variableName);
	const std::vector<std::string>& getVariables() const;

private:	
	const char* input;	
	std::vector<std::string> variables;
	static const short MAX_VARIABLES_NUMBER = 3;

protected:
	virtual std::string parseToken();
	virtual Expression parseSimpleExpression();
	virtual Expression parseBinaryExpression(int min_priority);

	static int getPriority(const std::string& binary_op);

	const char* getInputString() const;
	void setInputString(const char* str);
};

template<class T>
Parser<T>::Parser(const char* _input, std::vector<std::string> &vars){
	if (vars.size() > MAX_VARIABLES_NUMBER){
		throw std::runtime_error("Parser: Too many variables");
	}
	input = _input;

		for (unsigned int i = 0; i < vars.size(); ++i){
			std::regex pattern("[a-z]{1,3}");
			if (!std::regex_match(vars.at(i), pattern)) {			
				throw std::runtime_error("Parser: Incorrect variable name");
			}
			variables.push_back(vars.at(i));
		}
		
	
}	

template<class T>Parser<T>::Parser(){
	input = nullptr;
	variables.push_back("x");
}

template<class T>
Parser<T>::~Parser(){	// virtual destructor
}		

template<class T> 
Expression Parser<T>::parse(const char* _inputStream = 0){	// virtual parse function	
	if (_inputStream){
		this->input = _inputStream;
	}
	if (this->input == nullptr)
		throw std::invalid_argument("Parser: Empty parse string");
	const char* inputClone = this->input;
	Expression res = this->parseBinaryExpression(0);
	if ((*(this->input)) != '\0')
		throw std::invalid_argument("Parser: Wrong input string");
	this->input = inputClone;
	inputClone = nullptr;
	return res;
}

// accessors
template<class T>
const std::vector<std::string>& Parser<T>::getVariables() const{
	return this->variables;
}

template<class T>
bool Parser<T>::isContainsVariable(const char* variableName) const{

	for(unsigned int i = 0; i < variables.size(); ++i){
		if (std::strncmp(this->variables.at(i).c_str(), variableName, strlen(variableName)) == 0)
			return true;
	}
	return false;
}

template<class T> 
void Parser<T>::addVariable(const char* variableName){
	if (!isContainsVariable(variableName)){
		variables.push_back(variableName);	
	}
}

template<class T>
std::string Parser<T>::parseToken() {
	const int OPERATIONS_NUMBER = 18;
	while (std::isspace(*input)) ++input;	// skip spaces

	if (std::isdigit(*input)) {		// if number
		std::string number;
		while (std::isdigit(*input) || *input == '.') number.push_back(*input++);
		return number;
	}

	const std::string tokens[OPERATIONS_NUMBER] =
	{"+", "-", "**", "*", "/", "mod", "abs", "sin", "cos", "(", ")", "tg", "ctg", "ln", "arcsin", "arccos", "arctg", "arcctg"};

	//for (auto& t : tokens) {
	for (int i = 0; i < OPERATIONS_NUMBER; ++i) {
		const std::string &t = tokens[i];
		if (std::strncmp(input, t.c_str(), t.size()) == 0) {
			input += t.size();	
			return t;	// return operator token
		}
	}

	for (unsigned int i = 0; i < variables.size(); ++i){
		std::string var = variables.at(i);
		if (std::strncmp(input, var.c_str(), strlen(var.c_str())) == 0) {
			input += strlen(var.c_str());	
			return var;	
		}

	}

	return "";
}

template<class T> 
Expression Parser<T>::parseSimpleExpression() {
	auto token = parseToken();
	if (token.empty()) throw std::runtime_error("Parser: Invalid input");

	if (token == "(") {
		auto result = parseBinaryExpression(0);
		if (parseToken() != ")") throw std::runtime_error("Parser: Expected ')'");
		return result;
	}

	if (std::isdigit(token[0]))
		return Expression(token);

	for (unsigned int i = 0; i < variables.size(); ++i){
		const char* var = variables.at(i).c_str();
		if (std::strncmp(var, token.c_str(), token.size()) == 0){
			return Expression(token);
		}
	}	

	return Expression(token, parseSimpleExpression());
}

template<class T> 
Expression Parser<T>::parseBinaryExpression(int min_priority) {
	auto left_expr = parseSimpleExpression();

	for (;;) {
		auto op = parseToken();
		auto priority = getPriority(op);
		if (priority <= min_priority) {
			input -= op.size();	
			return left_expr;
		}

		auto right_expr = parseBinaryExpression(priority);	
		left_expr = Expression(op, left_expr, right_expr);
	}
}

template<class T> 
int Parser<T>::getPriority(const std::string& binary_op) {
	if ((binary_op == "+") || (binary_op == "-")) return 1;	
	if ((binary_op == "*") || (binary_op == "/") || (binary_op == "mod")) return 2;
	if (binary_op == "**") return 3;
	return 0;
}

// accessors
template<class T>
const char* Parser<T>::getInputString() const{
	return this->input;
}

template<class T>
void Parser<T>::setInputString(const char* str){
	this->input = str;
}