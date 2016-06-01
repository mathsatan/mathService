#include "Expression.h"

Expression::Expression():token(""){
}

Expression::Expression(std::string _token): token(_token){
}

Expression::Expression(std::string _token, Expression a){
	token = _token;
	args.push_back(a);
}

Expression::Expression(std::string _token, Expression a, Expression b){
	token = _token;
	args.push_back(a);
	args.push_back(b);
}
Expression::Expression(const Expression& rhs){
	this->args.swap(rhs.getArgs());
	this->token = rhs.getToken();
}
Expression& Expression::operator=(const Expression &rhs){
	if(this != &rhs){
		this->token = rhs.getToken();
		this->args.clear();
		this->args.swap(rhs.getArgs());
	}
	return *this;
} 

		// акссессоры
std::string Expression::getToken() const{
	return this->token;
}

std::vector<Expression> Expression::getArgs() const{
	return this->args;
}