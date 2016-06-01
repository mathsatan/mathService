#include "Integrate.h"
#include "Differentiate.h"
#include "LatexParser.h"
#include <math.h>


#include <limits>
#include <ctime>

/******protocol*********
Параметры:
1) Режим: int, div
2) Функция (без пробелов)
3) a (если интегрируем) или порядок производной (если дифференцируем)
4) b (если интегрируем)

Успешно (return 0)
1) Исходная функция (в латексе)
2) Результат (в латексе)
3) Время работы (в миллисекундах)

Неудачно (return sign int):
1) Сообщение ошибки/исключения
***********************/

/*** TESTING
3) Некорректный ввод
sin
-sin
ln(sin)
asd
sin(1.3344.67)
45\sdf

4) 
Expression("sin") Unknown expression type
Expression("+") Unknown expression type
Expression("sin", Expression("1"), Expression("2")) return 0;

Expression("2", Expression("+"), Expression("1")) return 0;
Expression("qwe") Unknown expression type
Expression("x", Expression("x"), Expression("x")) Unknown binary operator
Expression("1", Expression("1"), Expression("1")) return 0;
Expression("4", Expression("y"), Expression("0")) return 0;
***/

inline void integrateFunction(const Expression &function, const double a, const double b){
	std::map<std::string, double> variablesList;
	variablesList.insert(std::pair<std::string, double>("x", 1.0f));
	variablesList.insert(std::pair<std::string, double>("y", 1.0f));
	variablesList.insert(std::pair<std::string, double>("z", 1.0f));

	Calculate<double>* integrate = new Integrate(variablesList, a, b);
	auto result = integrate->calc(function);
	delete integrate;

	if (_finite(result)){
		std::cout << result << std::endl;
	}else if (std::numeric_limits<double>::infinity() == result){
		std::cout << "\\infty" << '\n';
	}else if (-std::numeric_limits<double>::infinity() == result){
		std::cout << "-\\infty" << '\n';			
	}else if (_isnan(result)){
		std::cout << "NaN" << '\n';			
	}	
}

inline void differentiateFunction(const Expression &function, Parser<std::string> *parser, const int order = 1){
	Parser<Expression> *differentiate = new Differentiate();
	Expression ex = differentiate->eval(function);
	for(int i = 0; i < order - 1; ++i){
		Expression temp = differentiate->eval(ex);
		ex = temp;
	}
	std::cout << parser->eval(ex) << std::endl;
	delete differentiate;
}

int main(int argc, char *argv[]){
	/*const char *str = "-3*sin(3*x)";
	try{
	Parser<std::string>* latex = new LatexParser();
	Parser<Expression> *differentiate = new Differentiate();
	std::cout << latex->eval(differentiate->eval(latex->parse(str))) << std::endl;
	delete differentiate;
	delete latex;
	}catch(std::exception& e){
		std::cout << e.what() << '\n';
	}
	return 0;*/

	if (!argv[1]){
		std::cout << "Error: Undefined program mode\n";
		return -1;
	}
	if (!argv[2]){
		std::cout << "Error: Function f(x) is undefined\n";
		return -2;
	}
	try{
		Parser<std::string>* latex = new LatexParser();
		Expression e = latex->parse(argv[2]);
		std::cout << latex->eval(e) << std::endl;
		unsigned int time =  clock();

		if (!strcmp(argv[1], "integral")){
			if (!argv[3] || !argv[4]){
				std::cout << "Error: Incorrect integrate ranges\n";
				return -3;
			}	
			integrateFunction(e, atof(argv[3]), atof(argv[4]));
		}else if (!strcmp(argv[1], "derivative")){
			if (!argv[3]){
				std::cout << "Error: Incorrect derivative order\n";
				return -4;
			}	
			differentiateFunction(e, latex, atoi(argv[3]));
		}else{
			std::cout <<"Error: Wrong mode\n";
			return -5;
		}
		delete latex;
		std::cout << (clock() - time) << '\n';
	}
	catch (std::exception& e) {
		std::cout <<"Exception: " << e.what() << '\n';
		return -6;
	}
	return 0;
}