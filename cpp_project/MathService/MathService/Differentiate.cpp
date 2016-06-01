#include "Differentiate.h"

Expression Differentiate::eval(const Expression& e) {
	switch (e.getArgs().size()) {
	case 2: {
		auto a = (e.getArgs())[0];
		auto b = (e.getArgs())[1];	
		bool u = isContainVars(a);
		bool v = isContainVars(b);
		/*if (!u && !v){ 	// c1*c2, c1 = c2 = const
			return Expression("0");
		}*/
		if (!e.getToken().compare("+")) return Expression("+", eval(a), eval(b)); 
		if (!e.getToken().compare("-")) return Expression("-", eval(a), eval(b));
		if (!e.getToken().compare("*")){	// (c*u(x))' = c*(u(x))', c = const			
			if (u && !v){ 	// u(x)*c, c = const
				return Expression("*", eval(a), b);
			}else if (!u && v){ 	// c*u(x), c = const
				return Expression("*", a, eval(b));
			}
			return Expression("+", Expression("*", eval(a), b), Expression("*", a, eval(b)));
		}
		if (!e.getToken().compare("/")){	// check!
			if (u && !v){ 	// u(x)/c, c = const
				return Expression("*", eval(a), Expression("/", Expression("1"), b));	// (u(x)/c)' = 1/c * (u(x))', c = const
			}
			return Expression("/", Expression("-", Expression("*", eval(a), b), Expression("*", a, eval(b))), Expression("*", b, b));
		}

		if (!e.getToken().compare("**")){ 	// check!
			if (u && !v){ 	// u(x)^c, c = const
				return Expression("*", Expression("*", Expression("**", a, Expression("-", b, Expression("1"))), eval(a)), b);
			}else if (!u && v){ // c^u(x), c=const
				return Expression("*", Expression("*", Expression("**", a, b), eval(b)), Expression("ln", a));
				}	// u(x)^v(x)
			return Expression("*", Expression("**", a, Expression("-", b, Expression("1"))), Expression("+", Expression("*", b, eval(a)), Expression("*", Expression("*", a, Expression("ln", a)), eval(b)))); 		
		}
		throw std::runtime_error("Differentiate: Unknown binary operator " + e.getToken());
	}
	case 1: {
		auto a = (e.getArgs())[0];		
		if (isContainConst(a)){
			return Expression("0");
		}
		if (!e.getToken().compare("sin")){ 
			if (isContainVar(a)){
				return Expression("cos", a); 
			}else{
				return Expression("*", Expression("cos", a), eval(a));	
			}	
		}else
		if (!e.getToken().compare("cos")) {
			if (isContainVar(a)){
				return Expression("-", Expression("sin", a));	
			}else{
				return Expression("*", Expression("-", Expression("sin", a)), eval(a));	
			}	
		}else
		if (!e.getToken().compare("ln")) {// check!
			if (isContainVar(a)){
				return Expression("/", Expression("1"), a);	
			}else{
				return Expression("*", Expression("-", Expression("/", Expression("1"), a)), eval(a));	
			}	
		}else
		if (!e.getToken().compare("tg")) {// check!
			//expression = Expression("/", Expression("1"), Expression("**", Expression("cos", a), Expression("2")));
			 return (isContainVar(a)) ? Expression("/", Expression("1"), Expression("**", Expression("cos", a), Expression("2"))):
				 Expression("*", Expression("/", Expression("1"), Expression("**", Expression("cos", a), Expression("2"))), eval(a));	 
				
		}else
		if (!e.getToken().compare("ctg")) {// check!
			return (isContainVar(a)) ? Expression("/", Expression("-", Expression("1")), Expression("**", Expression("sin", a), Expression("2"))):
				 Expression("*", Expression("/", Expression("-", Expression("1")), Expression("**", Expression("sin", a), Expression("2"))), eval(a));	 			
		}else
		if (!e.getToken().compare("-")){ 
			return (isContainVar(a)) ? Expression("-", eval(a)):
				 Expression("*",  Expression("-", eval(a)), eval(a));	 
		}else
		if (!e.getToken().compare("arcsin")){ 
			//expression = Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2")))));
			return (isContainVar(a)) ?  Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2"))))):
				 Expression("*", Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2"))))), eval(a));
		}else
		if (!e.getToken().compare("arccos")){ 
			//expression = Expression ("-", Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2"))))));
			return (isContainVar(a)) ? Expression ("-", Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2")))))):
				 Expression("*", Expression ("-", Expression("/", Expression("1"), Expression("sqrt", Expression("-", Expression("1"),Expression("**", a, Expression("2")))))), eval(a));
		}else
		if (!e.getToken().compare("arctg")) {// check!
			//expression = Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2"))));	
			return (isContainVar(a)) ? Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2")))):
				 Expression("*", Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2")))), eval(a));
		}else
		if (!e.getToken().compare("arcctg")) {// check!
			//expression = Expression ("-", Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2")))));
			return (isContainVar(a)) ? Expression ("-", Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2"))))):
				 Expression("*", Expression ("-", Expression("/", Expression("1"), Expression("+", Expression("1"),Expression("**", a, Expression ("2"))))), eval(a));
		}else if (!e.getToken().compare("abs")){ // check!
			//return Expression("/", Expression("*", eval(a), a), Expression("abs", a)); 
			return (isContainVar(a)) ? Expression("/", Expression("*", eval(a), a), Expression("abs", a)):
				 Expression("*", Expression("/", Expression("*", eval(a), a), Expression("abs", a)), eval(a));
		}	
			// default			
		throw std::runtime_error("Differentiate: Unknown unary operator" + e.getToken());
		
		//if (isContainVar(a)){
			//return expression; 
		//}
		//return Expression("*", expression, eval(a));
	}
	case 0:// token can be variable or constatant
		if (isContainVar(e.getToken())){
			return Expression("1");
		}
		if (isContainConst(e.getToken())){
			return Expression("0");
		}
	}
	throw std::runtime_error("Differentiate: Unknown expression type");
}

bool Differentiate::isContainVar(const Expression& e)const{
	const std::vector<std::string>& vars = this->getVariables();
	for(unsigned int i = 0; i < vars.size(); ++i){
		if (!e.getArgs().size() && !e.getToken().compare(vars.at(i))){
			return true;
		}			
	}
		
	return false;
}

bool Differentiate::isContainConst(const Expression& e)const{
	const std::string token = e.getToken().c_str();	// is const?
	if ((e.getArgs().size() == 0) && (std::isdigit(*(token.c_str()))))
		return true;
	return false;
}

bool Differentiate::isContainVars(const Expression& ex){
	std::stack<Expression> st;	
	st.push(ex);
	while(!st.empty()){
		Expression e = st.top();
		st.pop();
		const std::vector<std::string>& vars = this->getVariables();
		for(unsigned int i = 0; i < vars.size(); ++i){
			if(!e.getToken().compare(vars.at(i))) 
				return 1;
		}
		
		for(unsigned int i = 0; i < e.getArgs().size(); ++i){
			st.push(e.getArgs().at(i));
		}
	}
	return 0;
}

// virtual destructor
Differentiate::~Differentiate(){
}