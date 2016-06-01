#include "Expression.h"
#include <map>
#include <regex>

#pragma once

template<class T>
class Calculate
{
public:
	Calculate(){}
	virtual ~Calculate(){}
	virtual T calc(const Expression&) = 0;	

	const std::map<std::string, T>& getVariables()const;
	void setVariables(const std::map<std::string, T> &vars);
	void setVariable(const std::string name, const double value);

protected:
	static const int MAX_VARIABLES_NUMBER = 3;
	std::map<std::string, T> variables;
};

template<class T>
void Calculate<T>::setVariable(const std::string name, const double value){
		auto it = this->variables.find(name);
		if (it != this->variables.end()) {
			it->second = value;		
		}
	}

template<class T>
const std::map<std::string, T>& Calculate<T>::getVariables()const{
	return this->variables;
}

template<class T>
void Calculate<T>::setVariables(const std::map<std::string, T> &vars){
	if (vars.size() > MAX_VARIABLES_NUMBER){
		throw std::runtime_error("Too many variables");
	}
	this->variables.clear();

	for (auto i = vars.begin(); i != vars.end(); ++i){
		if (!std::regex_match(i->first, std::regex("[a-z]{1,3}"))) {			
			throw std::runtime_error("Incorrect variable name");
		}
		this->variables.insert(*i);
	}
}