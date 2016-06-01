#include "Calculate.h"

#pragma once

class Integrate: public Calculate<double>
{
public:
	Integrate(const std::map<std::string, double>&, const double, const double);
	Integrate();
	virtual ~Integrate();
	virtual double calc(const Expression&);

private:
	double a;
	double b;
	double evaluate(const Expression&);
	bool isNumber(const std::string&);

};