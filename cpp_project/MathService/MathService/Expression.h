#include <vector>
#include <cctype>
#include <cstring>
#include <string>

#pragma once

class Expression {

protected:
	std::string token;
	std::vector<Expression> args;

public:
	Expression();
    Expression(std::string);
	Expression(std::string, Expression);
	Expression(std::string, Expression, Expression);
	Expression(const Expression&);
	Expression& operator=(const Expression&);
		// акссессоры
	std::string getToken() const;

	std::vector<Expression> getArgs() const;
};